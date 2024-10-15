<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

use App\EventListener\GameEndedEvent;
use App\Component\System\Guid;
use App\Component\Rules\Backgammon\Game;
use App\Component\Ai\Backgammon\Engine as AiEngine;
use App\Component\Dto\Mapper;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// Actions
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\DoublingActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;

abstract class AbstractGameManager implements GameManagerInterface
{
    /** @const int */
    const firstBet = 50;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    
    /** @var ManagerRegistry */
    protected $doctrine;
    
    /** @var RepositoryInterface */
    protected $gameRepository;
    
    /** @var RepositoryInterface */
    protected $gamePlayRepository;
    
    /** @var FactoryInterface */
    protected $gamePlayFactory;
    
    /** @var RepositoryInterface */
    protected $playersRepository;
    
    /** @var RepositoryInterface */
    protected $tempPlayersRepository;
    
    /** @var FactoryInterface */
    protected $tempPlayersFactory;
    
    /** @var DeferredCancellation */
    protected $moveTimeOut;
    
    /** @var Game */
    public $Game;
    
    /** @var AiEngine | null */
    public $Engine = null;
    
    /** @var bool */
    public $SearchingOpponent;
    
    /** @var WebSocket */
    public $Client1;
    
    /** @var WebSocket */
    public $Client2;
    
    /** @var string */
    public $Inviter;
    
    /** @var string */
    public $GameCode;
    
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        RepositoryInterface $tempPlayersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $forGold
    ) {
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        
        $this->Game     = Game::Create( $forGold );
        $this->Created  = new \DateTime( 'now' );
    }
    
    public function getClient( $clientId ): mixed
    {
        if ( $this->Client1->getClientId() == $clientId ) {
            return $this->Client1;
        }
        
        if ( $this->Client2->getClientId() == $clientId ) {
            return $this->Client2;
        }
        
        throw new \Exception( 'GameManager Websocket Client Not Found !!!' );
    }
    
    public function Restore( PlayerColor $color, WebsocketClientInterface $socket ): void
    {
        $gameDto = Mapper::GameToDto( $this->Game );
        $restoreAction = new GameRestoreActionDto();
        $restoreAction->game = $gameDto;
        $restoreAction->color = $color;
        $restoreAction->dices = $this->Game->Roll->map(
            function( $entry ) {
                return Mapper::DiceToDto( $entry );
            }
        )->toArray();
        
        if ( $color == PlayerColor::Black ) {
            $this->Client1 = $socket;
            $otherSocket = $this->Client2;
        } else {
            $this->Client2 = $socket;
            $otherSocket = $this->Client1;
        }
        
        $this->Send( $socket, $restoreAction );
        //Also send the state to the other client in case it has made moves.
        if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
            $restoreAction->color = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
            $this->Send( $otherSocket, $restoreAction );
        } else {
            $this->logger->warning( "Failed to send restore to other client" );
        }
    }
    
    public function DoAction( ActionNames $actionName, string $actionText, WebsocketClientInterface $socket, WebsocketClientInterface $otherSocket )
    {
        $this->logger->info( "Doing action: {$actionName}" );
        
        if ( $actionName == ActionNames::movesMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = \json_decode( $actionText );
            
            if ( $socket == $this->Client1 ) {
                $this->Game->BlackPlayer->FirstMoveMade = true;
            } else {
                $this->Game->WhitePlayer->FirstMoveMade = true;
            }
            
            $this->DoMoves( $action );
            $this->NewTurn( $socket );
                    
        } else if ( $actionName == ActionNames::opponentMove ) {
            $action = \json_decode( $actionText );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::undoMove ) {
            $action = \json_decode( $actionText );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::rolled ) {
            $action = \json_decode( $actionText );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::requestedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \Exception( "requestedDoubling should not be possible in a non gold game" );
            }
            
            $action = \json_decode( $actionText );
            $action->moveTimer = Game::ClientCountDown;
            
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $this->Game->SwitchPlayer();
            if ( $this->AisTurn() ) {
                if ( $this->Engine->AcceptDoubling() ) {
                    $this->DoDoubling();
                    $this->Game->SwitchPlayer();
                    
                    yield delay( 2000 );
                    $doublingAction = new DoublingActionDto();
                    $doublingAction->actionName = ActionNames::acceptedDoubling->value;
                    $doublingAction->moveTimer = Game::ClientCountDown;
                    
                    $this->Send( $socket, $doublingAction );
                } else {
                    yield delay( 2000 );
                    $this->Resign( $this->Game->OtherPlayer() );
                }
            } else {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::acceptedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \Exception( "acceptedDoubling should not be possible in a non gold game" );
            }
            
            $action = \json_decode( $actionText );
            $action->moveTimer = Game::ClientCountDown;
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $this->DoDoubling();
            $this->Game->SwitchPlayer();
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::requestHint ) {
            if ( ! $this->Game->IsGoldGame && $this->Game->CurrentPlayer == PlayerColor::Black ) {
                // Aina is always white
                $action = $this->GetHintAction();
                $this->Send( $socket, $action );
            }
        } else if ( $actionName == ActionNames::connectionInfo ) {
            $action = \json_decode( $actionText );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::resign ) {
            $winner = $this->Client1 == $otherSocket ? PlayerColor::Black : PlayerColor::White;
            $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            $this->CloseConnections( $socket );
        }
    }
    
    public function Send( WebsocketClientInterface $socket, object $obj ): void
    {
        if ( $socket == null || $socket->State != WebSocketState::Open ) {
            $this->logger->info( "Cannot send to socket, connection was lost." );
            return;
        }
        
        $json = $this->serializer->serialize( $obj, 'json' );
        $this->logger->info( "Sending to client {$json}" );
        
        try {
            $socket->send( $obj );
        } catch ( \Exception $exc ) {
            $this->logger->error( "Failed to send socket data. Exception: {$exc->getMessage()}" );
        }
    }
    
    protected function StartGame(): void
    {
        //$this->logger->info( 'MyDebug: Begin Start Game' );
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $gameDto = Mapper::GameToDto( $this->Game );
        
        $action = new GameCreatedActionDto();
        $action->game       = $gameDto;
        $action->myColor    = PlayerColor::Black;
        $this->Send( $this->Client1, $action );
        
//         $action->myColor = PlayerColor::White;
//         $this->Send( $this->Client2, $action );
        
        $this->Game->PlayState = GameState::FirstThrow;
        // todo: visa på clienten även när det blir samma
        
        while ( $this->Game->PlayState == GameState::FirstThrow ) {
            //$this->logger->info( 'MyDebug: Entering Play State Loop' );
            
            $this->Game->RollDice();
            $rollAction = new DicesRolledActionDto();
            $rollAction->dices = $this->Game->Roll->map(
                function( $entry ) {
                    return Mapper::DiceToDto( $entry );
                }
            );
            $rollAction->playerToMove = $this->Game->CurrentPlayer;
            $rollAction->validMoves = $this->Game->ValidMoves->map(
                function( $entry ) {
                    return Mapper::MoveToDto( $entry );
                }
            );
            $rollAction->moveTimer = Game::ClientCountDown;
                
            $this->Send( $this->Client1, $rollAction );
//             $this->Send( $this->Client2, $rollAction );

            //$this->logger->info( 'MyDebug: ' . $this->serializer->serialize( $rollAction, 'json' ) );
        }
        //$this->logger->info( 'MyDebug: Exited From Play State Loop' );
        
        /*  
        $this->moveTimeOut = new DeferredCancellation();
        Utils::RepeatEvery( 500, () =>
        {
            TimeTick();
        }, $this->moveTimeOut );
        */
    }
    
    protected function TimeTick(): void
    {
        if ( ! $this->moveTimeOut->IsCancellationRequested ) {
            $ellapsed = ( new \DateTime( 'now' ) ) - $this->Game->ThinkStart;
            if ( $ellapsed->TotalSeconds > $this->Game->TotalThinkTime ) {
                $this->logger->info( "The time run out for {$this->Game->CurrentPlayer}" );
                $this->moveTimeOut->cancel();
                $winner = $this->Game->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                $this->EndGame( $winner );
            }
        }
    }
    
    protected function EndGame( PlayerColor $winner )
    {
        $this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::Ended;
        $this->Logger->info( "The winner is {$winner}" );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner, $newScore );
        $this->eventDispatcher->dispatch( new GameEndedEvent( Mapper::GameToDto( $this->Game ) ), GameEndedEvent::NAME );
    }
    
    protected function SendNewRoll(): void
    {
        $this->Game->RollDice();
        $rollAction = new DicesRolledActionDto();
        $rollAction->dices = $this->Game->Roll->map(
            function( $entry ) {
                return Mapper::DiceToDto( $entry );
            }
        );
        $rollAction->playerToMove = $this->Game->CurrentPlayer;
        $rollAction->validMoves = $this->Game->ValidMoves->map(
            function( $entry ) {
                return Mapper::MoveToDto( $entry );
            }
        );
        $rollAction->moveTimer = Game::ClientCountDown;
        
        if ( $this->Client1 && ! $this->Game->BlackPlayer->IsAi() ) {
            $this->Send( $this->Client1, $rollAction );
        }
        
        if ( $this->Client2 && ! $this->Game->WhitePlayer->IsAi() ) {
            $this->Send( $this->Client2, $rollAction );
        }
    }
    
    protected function IsAi( $id ): bool
    {
        return false; // id.ToString().Equals( Player.AiUser, StringComparison.OrdinalIgnoreCase );
    }
    
    protected function CreateDbGame(): void
    {
        $blackUser = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        
        if ( $this->Game->IsGoldGame && $blackUser->getGold() < self::firstBet ) {
           //throw new \Exception( "Black player dont have enough gold" ); // Should be guarder earlier
        }
            
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $blackUser->getId() ) ) {
            $blackUser->setGold( self::firstBet );
        }
                
        $black = $this->tempPlayersFactory->createNew();
        $black->setGuid( Guid::NewGuid() );
        $black->setPlayer( $blackUser );
        $black->setColor( PlayerColor::Black->value );
        $black->setName( $blackUser->getName() );
        $blackUser->addGamePlayer( $black );
        
        $whiteUser = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
        if ( $this->Game->IsGoldGame && $whiteUser->getGold() < self::firstBet ) {
            //throw new \Exception( "White player dont have enough gold" ); // Should be guarder earlier
        }
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $whiteUser->getId() ) ) {
            $whiteUser->setGold( self::firstBet );
        }
            
        $white = $this->tempPlayersFactory->createNew();
        $white->setGuid( Guid::NewGuid() );
        $white->setPlayer( $whiteUser );
        $white->setColor( PlayerColor::White->value );
        $white->setName( $whiteUser->getName() );
        $whiteUser->addGamePlayer( $white );
        
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( Guid::NewGuid() );
        
        $black->setGame( $game );
        $white->setGame( $game );
        
        $game->addGamePlayer( $black );
        $game->addGamePlayer( $white );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
    }
    
    protected function SaveWinner( PlayerColor $color ): ?array
    {
        if ( ! $this->Game->ReallyStarted() ) {
            $this->ReturnStakes();
            return null;
        }
        
        $em     = $this->doctrine->getManager();
        $dbGame = $this->gamePlayRepository->find( $this->Game->Id );
        if ( $dbGame->getWinner() ) { // extra safety
            return [null, null];
        }
            
        $black = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        $white = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
        $computed = $this->Score->NewScore( $black->getElo(), $white->getElo(), $black->getGameCount(), white->getGameCount(), PlayerColor::Black );
        $blackInc = 0;
        $whiteInc = 0;
        
        $black->increaseGameCount();
        $white->increaseGameCount();
        $dbGame->setWinner( $color );
        
        if ( $this->Game->IsGoldGame )
        {
            $blackInc = $computed['black'] - $black->getElo();
            $whiteInc = $computed['white'] - $white->getElo();
            
            $black->setElo( $computed['black'] );
            $white->setElo( $computed['white'] );
            
            $stake = $this->Game->Stake;
            $this->Game->Stake = 0;
            $this->logger->info( "Stake" . $stake );
            $this->logger->info( "Initial gold: {$black->getGold()} {$this->Game->BlackPlayer->Gold} {$white->getGold()} {$this->Game->WhitePlayer->Gold}" );
            
            if ( $color == PlayerColor::Black ) {
                if ( ! $this->IsAi( $black->getId() ) )
                    $black->addGold( $stake );
                    $this->Game->BlackPlayer->Gold += stake;
            } else {
                if ( ! $this->IsAi( $white->getId() ) )
                    $white->addGold( $stake );
                    $this->Game->WhitePlayer->Gold += stake;
            }
            $this->logger->info( "After transfer: {$black->Gold} {$this->Game->BlackPlayer->Gold} {$white->Gold} {$this->Game->WhitePlayer->Gold}" );
        }
        
        $em->persist( $black );
        $em->persist( $white );
        $em->persist( $dbGame );
        $em->push();
        
        if ( $this->Game->IsGoldGame ) {
            $scoreBlack = new NewScoreDto();
            $scoreBlack->score = $black->getElo();
            $scoreBlack->increase = $blackInc;
            
            $scoreWhite = new NewScoreDto();
            $scoreWhite->score = $white->getElo();
            $scoreWhite->increase = $whiteInc;
            
            return [$scoreBlack, $scoreWhite];
        } else {
            return [null, null];
        }
    }
    
    protected function GetWinner(): ?PlayerColor
    {
        $winner = null;
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
            if (
                $this->Game->GetHome( PlayerColor::Black )->Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::Black;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::Ended;
                $winner = PlayerColor::Black;
            }
        } else {
            if (
                $this->Game->GetHome( PlayerColor::Black )->Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::White;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::Ended;
                $winner = PlayerColor::White;
            }
        }
        
        return $winner;
    }
    
    protected function SendWinner( PlayerColor $color, ?array $newScore ): void
    {
        $game = Mapper::GameToDto( $this->Game );
        $game->winner = $color;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        $gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        if ( $this->Client1 ) {
            $this->Send( $this->Client1, $gameEndedAction );
        }
        
        $gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        if ( $this->Client2 ) {
            $this->Send( $this->Client2, $gameEndedAction );
        }
    }
    
    protected function ReturnStakes(): void
    {
        $em     = $this->doctrine->getManager();
        $black  = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        $white  = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
        
        if ( ! $this->IsAi( $black->getId() ) ) {
            $black->Gold += $this->Game->Stake / 2;
            $em->persist( $black );
        }
        
        if ( ! $this->IsAi( $white->getId() ) ) {
            $white->Gold += $this->Game->Stake / 2;
            $em->persist( $white );
        }
            
        $em->push();
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket )
    {
        if ( $socket != null ) {
            $this->logger->info( "Closing client" );
            //await socket.CloseAsync(WebSocketCloseStatus.NormalClosure, "Game aborted by client", CancellationToken.None);
            
            $socket->close();
        }
    }
    
    protected function Resign( PlayerColor $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->info( "{$winner} won Game {$this->Game->Id} by resignition.");
    }
    
    protected function NewTurn( WebsocketClientInterface $socket )
    {
        $winner = $this->GetWinner();
        $this->Game->SwitchPlayer();
        if ( $winner && $winner->HasValue ) {
            $this->EndGame( $winner->Value );
        } else {
            $this->SendNewRoll();
            
            if ( $this->AisTurn() ) {
                $this->EnginMoves( $socket );
            }
        }
    }
    
    protected function AisTurn(): bool
    {
        $plyr = $this->Game->CurrentPlayer == PlayerColor::Black ? $this->Game->BlackPlayer : $this->Game->WhitePlayer;
        return $plyr->IsAi();
    }
    
    protected function EnginMoves( WebsocketClientInterface $client )
    {
        \usleep( \rand( 700, 1200 ) );
        $action = new ActionDto();
        $action->actionName = ActionNames::rolled->value;
        $this->Send( $client, $action );
        
        $moves = $this->Engine->GetBestMoves();
        $noMoves = true;
        for ( $i = 0; $i < $moves->count(); $i++ ) {
            $move = $moves[$i];
            if ( $move == null ) {
                continue;
            }
            
            \usleep( \rand( 700, 1200 ) );
            $moveDto = Mapper::MoveToDto( $move );
            $moveDto->animate = true;
            $dto = new OpponentMoveActionDto();
            $dto->move = $moveDto;
            $this->Game->MakeMove( $move );
            if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
                    $this->Game->BlackPlayer->FirstMoveMade = true;
            } else {
                $this->Game->WhitePlayer->FirstMoveMade = true;
            }
                
            $noMoves = false;
            $this->Send( $client, $dto );
        }
        
        if ( $noMoves ) {
            \usleep( 2500 ); // if turn is switch right away, ui will not have time to display dice.
        }
        
        $this->NewTurn( $client );
    }
    
    public static function GetJsonError()
    {
        switch ( \json_last_error() ) {
            case JSON_ERROR_NONE:
                return ' - No errors';
                break;
            case JSON_ERROR_DEPTH:
                return ' - Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                return ' - Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                return ' - Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                return ' - Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                return ' - Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
            default:
                return ' - Unknown error';
                break;
        }
    }
}
