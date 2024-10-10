<?php namespace App\Component\Manager;

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
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\DoublingActionDto;

use App\Entity\GamePlayer;

class GameManager
{
    /** @const int */
    const firstBet = 50;
    
    /** @var LoggerInterface */
    protected $logger;
    
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
    
    /** @var \DateTime */
    protected $Created;
    
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
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        
//         $this->Game     = Game::Create( $forGold );
//         $this->Created  = new \DateTime( 'now' );
    }
    
    protected function StartGame(): void
    {
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
        }
        
        /*
        $this->moveTimeOut = new DeferredCancellation();
        Utils::RepeatEvery(500, () =>
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
        $this->Game->PlayState = GameState::ended;
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
        )->toArray();
        $rollAction->playerToMove = $this->Game->CurrentPlayer;
        $rollAction->validMoves = $this->Game->ValidMoves->map(
            function( $entry ) {
                return Mapper::MoveToDto( $entry );
            }
        )->toArray();
        $rollAction->moveTimer = Game::ClientCountDown;
        
        if ( ! $this->Game->BlackPlayer->IsAi() ) {
            $this->Send( $this->Client1, $rollAction );
        }
        
        if ( ! $this->Game->WhitePlayer->IsAi() ) {
            Send( $this->Client2, $rollAction );
        }
    }
    
    protected function IsAi( $id ): bool
    {
        return false; // id.ToString().Equals( Player.AiUser, StringComparison.OrdinalIgnoreCase );
    }
    
    public function Send( WebsocketClientInterface $socket, object $obj ): void
    {
        if ( $socket == null || $socket->State != WebSocketState::Open ) {
            $this->logger->info( "Cannot send to socket, connection was lost." );
            return;
        }
        
        $json = \json_encode( $obj );
        $this->logger->info( "Sending to client {$json}" );
        
        try
        {
            $socket->send( $obj );
        }
        catch ( \Exception $exc )
        {
            $this->logger->error( "Failed to send socket data. Exception: {$exc->getMessage()}" );
        }
    }
    
    public function ConnectAndListen( WebsocketClientInterface $webSocket, PlayerColor $color, UserInterface $dbUser, bool $playAi )
    {
        $webSocket->State   = WebSocketState::Open;
    }
    
    protected function CreateDbGame(): void
    {
        $blackUser = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        
        if ( $this->Game->IsGoldGame && $blackUser->getGold() < self::firstBet ) {
            // throw new \Exception( "Black player dont have enough gold" ); // Should be guarder earlier
        }
            
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $blackUser->getId() ) ) {
            //$blackUser->Gold -= self::firstBet;
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
            //$whiteUser.Gold -= firstBet;
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
    
    protected function Restore( PlayerColor $color, WebsocketClientInterface $socket ): void
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
        
        $this->ListenOn( $socket );
    }
    
    protected function ListenOn( WebsocketClientInterface $socket ): void
    {
        $socket->subscribe( "realm1", "game", \Closure::fromCallable( [$this, 'DoAction'] ) );
        
        
        /*
        while (
            $socket->State != WebSocketState::Closed &&
            $socket->State != WebSocketState::Aborted &&
            $socket->State != WebSocketState::CloseReceived
        ) {
            $text = $this->ReceiveText( $socket );
            if ( $text != null && ! empty( $text ) ) {
                $this->logger->info( "Received: {$text}" );
                
                try
                {
                    $action = \json_decode( $text );
                    $otherClient = $socket == $this->Client1 ? $this->Client2 : $this->Client1;
                    
                    // PHP Way to Call Async Methods
                    async( \Closure::fromCallable( [$this, 'DoAction'] ), [
                        $action->actionName,
                        $text,
                        $socket,
                        $otherClient
                    ])->await();
                }
                catch ( \Exception $e )
                {
                    $this->logger->error( "Failed to parse Action text {$e->getMessage()}" );
                }
            }
        }
        */
    }
    
    protected function ReceiveText( WebsocketClientInterface $socket ): string
    {
        return $socket->receive();
    }
    
    public function DoAction( ActionNames $actionName, string $actionText, WebsocketClientInterface $socket, WebsocketClientInterface $otherSocket )
    {
        $this->logger->info( "Doing action: {$actionName}" );
        
        if ( $actionName == ActionNames::movesMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = \json_decode( $actionText );
            if ( $socket == $this->Client1 )
                $this->Game->BlackPlayer->FirstMoveMade = true;
            else
                $this->Game->WhitePlayer->FirstMoveMade = true;
            
            $this->DoMoves( $action );
            //async( \Closure::fromCallable( [$this, 'NewTurn'] ), [$socket] )->await();
                    
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
            if ( ! $this->Game->IsGoldGame )
                throw new \Exception( "requestedDoubling should not be possible in a non gold game" );
                
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
                    $doublingAction->actionName = ActionNames::acceptedDoubling;
                    $doublingAction->moveTimer = Game::ClientCountDown;
                        
                    $this->Send( $socket, $doublingAction );
                } else {
                    yield delay( 2000 );
                    //async( \Closure::fromCallable( [$this, 'Resign'] ), [$this->Game->OtherPlayer()] )->await();
                }
            } else {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::acceptedDoubling ) {
            if ( ! $this->Game->IsGoldGame )
                throw new \Exception( "acceptedDoubling should not be possible in a non gold game" );
            
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
            //async( \Closure::fromCallable( [$this, 'CloseConnections'] ), [$socket] )->await();
        }
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
                $this->Game->PlayState = GameState::ended;
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
                $this->Game->PlayState = GameState::ended;
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
        //async( \Closure::fromCallable( [$this, 'Send'] ), [$this->Client1, $gameEndedAction] )->await();
        
        $gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        //async( \Closure::fromCallable( [$this, 'Send'] ), [$this->Client2, $gameEndedAction] )->await();
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
    
    protected function Resign( PlayerColor $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->info( "{$winner} won Game {$this->Game->Id} by resignition.");
    }
}