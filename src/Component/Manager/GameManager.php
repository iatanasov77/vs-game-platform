<?php namespace App\Component\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

use function Amp\async;
use function Amp\delay;
use Amp\DeferredCancellation;

use App\EventListener\GameEndedEvent;
use App\Component\System\Guid;
use App\Component\System\Db;
use App\Component\Rules\Backgammon\Game;
use App\Component\Ai\Backgammon\Engine as AiEngine;
use App\Component\Dto\Mapper;
use App\Component\Websocket\WebsocketClient;
use App\Component\Websocket\WebSocketState;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// Actions
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\DoublingActionDto;

use App\Entity\GamePlayer;

final class GameManager
{
    /** @const int */
    const firstBet = 50;
    
    /** @var LoggerInterface */
    private $logger;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gameRepository;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var RepositoryInterface */
    private $tempPlayersRepository;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    /** @var \DateTime */
    private $Created;
    
    /** @var DeferredCancellation */
    private $moveTimeOut;
    
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
    }
    
    private function StartGame(): void
    {
        $this->Game         = Game::Create( $forGold );
        $this->Created      = new \DateTime( 'now' );
        $this->moveTimeOut  = new DeferredCancellation();
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $gameDto = Mapper::GameToDto( $this->Game );
        
        $action = new GameCreatedActionDto();
        $action->game       = $gameDto;
        $action->myColor    = PlayerColor::black;
        $this->Send( $this->Client1, $action );
        
        $action->myColor = PlayerColor::white;
        $this->Send( $this->Client2, $action );
        
        $this->Game->PlayState = GameState::firstThrow;
        // todo: visa på clienten även när det blir samma
        
        while ( $this->Game->PlayState == GameState::firstThrow ) {
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
            $rollAction->moveTimer = $this->Game->ClientCountDown;
                
            $this->Send( $this->Client1, $rollAction );
            $this->Send( $this->Client2, $rollAction );
        }
        
        /*
        $this->moveTimeOut = new DeferredCancellation();
        Utils::RepeatEvery(500, () =>
        {
            TimeTick();
        }, $this->moveTimeOut );
        */
    }
    
    private function TimeTick(): void
    {
        if ( ! $this->moveTimeOut->IsCancellationRequested ) {
            $ellapsed = ( new \DateTime( 'now' ) ) - $this->Game->ThinkStart;
            if ( $ellapsed->TotalSeconds > $this->Game->TotalThinkTime ) {
                $this->logger->info( "The time run out for {$this->Game->CurrentPlayer}" );
                $this->moveTimeOut.cancel();
                $winner = Game.CurrentPlayer == Player.Color.Black ? PlayerColor.white : PlayerColor.black;
                $this->EndGame( $winner );
            }
        }
    }
    
    private function EndGame( PlayerColor $winner )
    {
        $this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->Logger->info( "The winner is {$winner}" );
        
        $newScore = $this->SaveWinner( $winner );
        async( \Closure::fromCallable( [$this, 'SendWinner'] ), [$winner, $newScore] )->await();
        $this->eventDispatcher->dispatch( new GameEndedEvent( Mapper::GameToDto( $this->Game ) ), GameEndedEvent::NAME );
    }
    
    private function SendNewRoll(): void
    {
        $this->Game->RollDice();
        $rollAction = new DicesRolledActionDto();
        $rollAction->dices = Game.Roll->map(
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
        $rollAction->moveTimer = $this->Game->ClientCountDown;
        
        if ( ! $this->Game->BlackPlayer->IsAi() )
            $this->Send( $this->Client1, $rollAction );
        
        if ( ! $this->Game->WhitePlayer->IsAi() )
            Send( $this->Client2, $rollAction );
    }
    
    private function IsAi( $id ): bool
    {
        return false; // id.ToString().Equals( Player.AiUser, StringComparison.OrdinalIgnoreCase );
    }
    
    public function Send( WebSocketClient $socket, object $obj ): void
    {
        if ( $socket == null || $socket->State != WebSocketState::Open ) {
            $this->logger->info( "Cannot send to socket, connection was lost." );
            return;
        }
        
        $json = \json_encode( $obj );
        $this->logger->info( "Sending to client {$json}" );
        
        try
        {
            async( \Closure::fromCallable( [$socket, 'send'] ), [$obj] )->await();
        }
        catch ( \Exception $exc )
        {
            $this->logger->error( "Failed to send socket data. Exception: {$exc->getMessage()}" );
        }
    }
    
    public function ConnectAndListen( WebSocketClient $webSocket, PlayerColor $color, UserInterface $dbUser, bool $playAi )
    {
        if ( $color == PlayerColor::Black ) {
            $this->Client1 = $webSocket;
            $this->Game->BlackPlayer->Id = $dbUser != null ? $dbUser->getId() : Guid::Empty();
            $this->Game->BlackPlayer->Name = $dbUser != null ? $dbUser->Name : "Guest";
            $this->Game->BlackPlayer->Photo = $dbUser != null && $dbUser->ShowPhoto ? $dbUser->PhotoUrl : "";
            $this->Game->BlackPlayer->Elo = $dbUser != null ? $dbUser->Elo : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->BlackPlayer->Gold = $dbUser != null ? $dbUser->Gold - firstBet : 0;
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $aiUser = Db.BgDbContext.GetDbUser(Player.AiUser);
                $this->Game->WhitePlayer->Id = $aiUser->Id;
                $this->Game->WhitePlayer->Name = $aiUser->Name;
                // TODO: AI image
                $this->Game->WhitePlayer->Photo = $aiUser->PhotoUrl;
                $this->Game->WhitePlayer->Elo = $aiUser->Elo;
                if ( $this->Game->IsGoldGame)
                    $this->Game->WhitePlayer->Gold = $aiUser->Gold;
                
                $this->Engine = new AiEngine( $this->Game );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::white )
                    async( \Closure::fromCallable( [$this, 'EnginMoves'] ), [$this->Client1] )->await();
            }
            
            async( \Closure::fromCallable( [$this, 'ListenOn'] ), [$webSocket] )->await();
        } else {
            if ( $playAi )
                throw new \Exception( "Ai always plays as white. This is not expected" );
            
            $this->Client2 = $webSocket;
            $this->Game->WhitePlayer->Id = $dbUser != null ? $dbUser->Id : Guid::Empty();
            $this->Game->WhitePlayer->Name = $dbUser != null ? $dbUser->Name : "Guest";
            $this->Game->WhitePlayer->Photo = $dbUser != null && $dbUser->ShowPhoto ? $dbUser->PhotoUrl : "";
            $this->Game->WhitePlayer->Elo = $dbUser != null ? $dbUser->Elo : 0;
            if ( $this->Game->IsGoldGame )
                $this->Game->WhitePlayer->Gold = $dbUser != null ? $dbUser->Gold - self::firstBet : 0;
            
            $this->CreateDbGame();
            $this->StartGame();
            
            async( \Closure::fromCallable( [$this, 'ListenOn'] ), [$webSocket] )->await();
        }
    }
    
    private function CreateDbGame(): void
    {
        $blackUser = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        
        if ( $this->Game->IsGoldGame && $blackUser->getGold() < self::firstBet )
            throw new \Exception( "Black player dont have enough gold" ); // Should be guarder earlier
            
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $blackUser->getId() ) ) {
            //$blackUser->Gold -= self::firstBet;
        }
                
        $black = $this->tempPlayersFactory->createNew();
        $black->setGuid( Guid::NewGuid() );
        $black->setPlayer( $blackUser );
        $black->setColor( PlayerColor::Black->value );
        $blackUser->addGamePlayer( $black );
        
        $whiteUser = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
        if ( $this->Game->IsGoldGame && $whiteUser->getGold() < self::firstBet )
            throw new \Exception( "White player dont have enough gold" ); // Should be guarder earlier
                    
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $whiteUser->getId() ) ) {
            //$whiteUser.Gold -= firstBet;
        }
            
        $white = $this->tempPlayersFactory->createNew();
        $white->setGuid( Guid::NewGuid() );
        $white->setPlayer( $whiteUser );
        $white->setColor( PlayerColor::White->value );
        $whiteUser->addGamePlayer( $white );
        
        $gameBase   = $this->gameRepository->findOneBy(['code' => $this->GameCode]);
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
    
    private function Restore( PlayerColor $color, WebsocketClient $socket ): void
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
        
        if ( $color == PlayerColor::black ) {
            $this->Client1 = $socket;
            $otherSocket = $this->Client2;
        } else {
            $this->Client2 = $socket;
            $otherSocket = $this->Client1;
        }
        
        async( \Closure::fromCallable( [$this, 'Send'] ), [$socket, $restoreAction] )->await();
        //Also send the state to the other client in case it has made moves.
        if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
            $restoreAction->color = $color == PlayerColor::black ? PlayerColor::white : PlayerColor::black;
            async( \Closure::fromCallable( [$this, 'Send'] ), [$otherSocket, $restoreAction] )->await();
        } else {
            $this->logger->warning( "Failed to send restore to other client" );
        }
        
        async( \Closure::fromCallable( [$this, 'ListenOn'] ), [$socket] )->await();
    }
    
    private function ListenOn( WebsocketClient $socket ): void
    {
        while (
            $socket->State != WebSocketState::Closed &&
            $socket->State != WebSocketState::Aborted &&
            $socket->State != WebSocketState::CloseReceived
        ) {
            $text = async( \Closure::fromCallable( [$this, 'ReceiveText'] ), [$socket])->await();
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
    }
    
    private function ReceiveText( WebsocketClient $socket ): string
    {
        return $socket->receive();
    }
    
    private function DoAction( ActionNames $actionName, string $actionText, WebSocketClient $socket, WebSocketClient $otherSocket )
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
            async( \Closure::fromCallable( [$this, 'NewTurn'] ), [$socket] )->await();
                    
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
            $action->moveTimer = $this->Game->ClientCountDown;
            
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $this->Game->SwitchPlayer();
            if ( $this->AisTurn() ) {
                if ( $this->Engine->AcceptDoubling() ) {
                    $this->DoDoubling();
                    $this->Game->SwitchPlayer();
                    
                    yield delay( 2000 );
                    $doublingAction = new DoublingActionDto();
                    $doublingAction->actionName = ActionNames::acceptedDoubling;
                    $doublingAction->moveTimer = $this->Game->ClientCountDown;
                        
                    $this->Send( $socket, $doublingAction );
                } else {
                    yield delay( 2000 );
                    async( \Closure::fromCallable( [$this, 'Resign'] ), [$this->Game->OtherPlayer()] )->await();
                }
            } else {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::acceptedDoubling ) {
            if ( ! $this->Game->IsGoldGame )
                throw new \Exception( "acceptedDoubling should not be possible in a non gold game" );
            
            $action = \json_decode( $actionText );
            $action->moveTimer = $this->Game->ClientCountDown;
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $this->DoDoubling();
            $this->Game->SwitchPlayer();
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::requestHint ) {
            if ( ! $this->Game->IsGoldGame && $this->Game->CurrentPlayer == PlayerColor::black ) {
                // Aina is always white
                $action = $this->GetHintAction();
                $this->Send( $socket, $action );
            }
        } else if ( $actionName == ActionNames::connectionInfo ) {
            $action = \json_decode( $actionText );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::resign ) {
            $winner = $this->Client1 == $otherSocket ? PlayerColor::black : PlayerColor::white;
            $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            async( \Closure::fromCallable( [$this, 'CloseConnections'] ), [$socket] )->await();
        }
    }
    
    private function SaveWinner( PlayerColor $color ): ?array
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
        $computed = $this->Score->NewScore( $black->getElo(), $white->getElo(), $black->getGameCount(), white->getGameCount(), PlayerColor::black );
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
            
            if ( $color == PlayerColor::black ) {
                if ( ! $this->IsAi( $black->getId() ) )
                    $black->addGold( $stake );
                    $this->Game->BlackPlayer->Gold += stake;
            } else {
                if ( ! $this->IsAi( $white->getId() ) )
                    $white->addGold( $stake );
                    $this->Game->WhitePlayer->Gold += stake;
            }
            $this->logger->info( "After transfer: {$black->Gold} {Game.BlackPlayer.Gold} {$white->Gold} {$this->Game->WhitePlayer->Gold}" );
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
    
    private function GetWinner(): ?PlayerColor
    {
        $winner = null;
        if ( $this->Game->CurrentPlayer == PlayerColor::black ) {
            if (
                $this->Game->GetHome( PlayerColor::black ).Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::black;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::ended;
                $winner = PlayerColor::black;
            }
        } else {
            if (
                $this->Game->GetHome( PlayerColor::black ).Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::white;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::ended;
                $winner = PlayerColor::white;
            }
        }
        
        return $winner;
    }
    
    private function SendWinner( PlayerColor $color, ?array $newScore ): void
    {
        $game = Mapper::GameToDto( $this->Game );
        $game->winner = $color;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        $gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        async( \Closure::fromCallable( [$this, 'Send'] ), [$this->Client1, $gameEndedAction] )->await();
        
        $gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        async( \Closure::fromCallable( [$this, 'Send'] ), [$this->Client2, $gameEndedAction] )->await();
    }
    
    private function ReturnStakes(): void
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
}