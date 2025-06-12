<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Rules\Backgammon\GameFactory as BackgammonRulesFactory;

use App\Component\System\Guid;
use App\Component\Rules\Backgammon\Game;
use App\Component\AI\Backgammon\Engine as AiEngine;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;
use App\Component\Utils\Keys;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\Actions\UndoActionDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\DoublingActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;
use App\Component\Dto\Actions\RolledActionDto;
use App\Component\Dto\Actions\StartGamePlayActionDto;
use App\Component\Dto\Actions\GamePlayStartedActionDto;
use App\Component\Dto\Actions\HintMovesActionDto;

use App\Entity\GamePlayer;

/**
 * See Logs:        sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket.log
 */
abstract class AbstractGameManager implements GameManagerInterface
{
    /** @const int */
    const firstBet = 50;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var bool */
    public $RoomSelected = false;
    
    /** @var string */
    protected $environement;
    
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var LiipImagineCacheManager */
    protected $imagineCacheManager;
    
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    
    /** @var ManagerRegistry */
    protected $doctrine;
    
    /** @var BackgammonRulesFactory */
    protected $backgammonRulesFactory;
    
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
    
    /** @var bool */
    public $ForGold;
    
    public function __construct(
        string $environement,
        LoggerInterface $logger,
        SerializerInterface $serializer,
        LiipImagineCacheManager $imagineCacheManager,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        BackgammonRulesFactory $backgammonRulesFactory,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        RepositoryInterface $tempPlayersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $forGold
    ) {
        $this->environement             = $environement;
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->imagineCacheManager      = $imagineCacheManager;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->backgammonRulesFactory   = $backgammonRulesFactory;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        $this->ForGold                  = $forGold;
    }
    
    public function setLogger( LoggerInterface $logger ): void
    {
        $this->logger   = $logger;
    }
    
    public function getClient( $clientId ): mixed
    {
        if ( $this->Client1->getClientId() == $clientId ) {
            $this->log( 'GameManager Websocket Client 1 Found !!!' );
            return $this->Client1;
        }
        
        if ( $this->Client2->getClientId() == $clientId ) {
            $this->log( 'GameManager Websocket Client 2 Found !!!' );
            return $this->Client2;
        }
        
        $this->log( 'GameManager Websocket Client Not Found !!!' );
        throw new \Exception( 'GameManager Websocket Client Not Found !!!' );
    }
    
    public function getPlayerPhotoUrl( GamePlayer $player ): ? string
    {
        if ( $player->getUser() ) {
            $url    = $this->imagineCacheManager->getBrowserPath(
                $player->getUser()->getInfo()->getAvatar()->getPath(),
                'users_crud_index_thumb',
            );
            
            return \parse_url( $url, PHP_URL_PATH );
        } else {
            return $player->getPhotoUrl();
        }
    }
    
    public function InitializeGame( string $gameCode ): void
    {
        switch ( $gameCode ) {
            case Keys::BACKGAMMON_NORMAL_KEY:
                $this->Game = $this->backgammonRulesFactory->createBackgammonNormalGame( $this->ForGold );
                break;
            case Keys::BACKGAMMON_TAPA_KEY:
                $this->Game = $this->backgammonRulesFactory->createBackgammonTapaGame( $this->ForGold );
                break;
            case Keys::BACKGAMMON_GULBARA_KEY:
                $this->Game = $this->backgammonRulesFactory->createBackgammonGulBaraGame( $this->ForGold );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $this->Created          = new \DateTime( 'now' );
    }
    
    public function dispatchGameEnded(): void
    {
        //$this->eventDispatcher->dispatch( new GameEndedEvent( Mapper::GameToDto( $this->Game ) ), GameEndedEvent::NAME );
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
            $this->log( "MyDebug: Failed to send restore to other client" );
        }
    }
    
    public function DoAction( ActionNames $actionName, string $actionText, WebsocketClientInterface $socket, ?WebsocketClientInterface $otherSocket )
    {
        $this->log( "MyDebug Doing action: {$actionName->value}" );
        
        if ( $actionName == ActionNames::movesMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, MovesMadeActionDto::class, JsonEncoder::FORMAT );
            
            if ( $socket == $this->Client1 ) {
                $this->Game->BlackPlayer->FirstMoveMade = true;
            } else {
                $this->Game->WhitePlayer->FirstMoveMade = true;
            }
            
            $this->DoMoves( $action );
            $this->NewTurn( $socket );
                    
        } else if ( $actionName == ActionNames::opponentMove ) {
            $action = $this->serializer->deserialize( $actionText, OpponentMoveActionDto::class, JsonEncoder::FORMAT );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::undoMove ) {
            $action = $this->serializer->deserialize( $actionText, UndoActionDto::class, JsonEncoder::FORMAT );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::rolled ) {
            $action = $this->serializer->deserialize( $actionText, ActionDto::class, JsonEncoder::FORMAT );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::requestedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \Exception( "requestedDoubling should not be possible in a non gold game" );
            }
            
            $action = $this->serializer->deserialize( $actionText, DoublingActionDto::class, JsonEncoder::FORMAT );
            $action->moveTimer = Game::ClientCountDown;
            
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $this->Game->SwitchPlayer();
            if ( $this->AisTurn() ) {
                if ( $this->Engine->AcceptDoubling() ) {
                    $this->DoDoubling();
                    $this->Game->SwitchPlayer();
                    
                    sleep( 2 );
                    $doublingAction = new DoublingActionDto();
                    $doublingAction->actionName = ActionNames::acceptedDoubling->value;
                    $doublingAction->moveTimer = Game::ClientCountDown;
                    
                    $this->Send( $socket, $doublingAction );
                } else {
                    sleep( 2 );
                    $this->Resign( $this->Game->OtherPlayer() );
                }
            } else {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::acceptedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \Exception( "acceptedDoubling should not be possible in a non gold game" );
            }
            
            $action = $this->serializer->deserialize( $actionText, DoublingActionDto::class, JsonEncoder::FORMAT );
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
            $action = $this->serializer->deserialize( $actionText, ConnectionInfoActionDto::class, JsonEncoder::FORMAT );
            $this->Send( $otherSocket, $action );
        } else if ( $actionName == ActionNames::resign ) {
            $winner = $this->Client1 == $otherSocket ? PlayerColor::Black : PlayerColor::White;
            $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            $this->log( 'MyDebug: exitGame action recieved from GameManager.' );
            $this->CloseConnections( $socket );
        } else if ( $actionName == ActionNames::startGamePlay ) {
            $this->log( 'MyDebug: startGamePlay action recieved from GameManager.' );
            $this->StartGamePlay();
        }
    }
    
    public function Send( ?WebsocketClientInterface $socket, object $obj ): void
    {
        //$this->log( 'MyDebug: Game ' . print_r( $obj, true ) );
        
        $sendObject = \get_class( $obj );
        $this->log( "MyDebug: Sending {$sendObject}." );
        
        if ( ! $socket || $socket->State != WebSocketState::Open ) {
            $this->log( "MyDebug: Cannot send to socket, connection was lost." );
            return;
        }
        
        // , [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]
        $json = $this->serializer->serialize( $obj, JsonEncoder::FORMAT );
        $this->log( "MyDebug: Sending to client {$json}" );
        
        try {
            $socket->send( $obj );
        } catch ( \Exception $exc ) {
            $this->log( "MyDebug: Failed to send socket data. Exception: {$exc->getMessage()}" );
        }
    }
    
    public function StartGame(): void
    {
        $this->log( 'MyDebug: Begin Start Game' );
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $gameDto = Mapper::GameToDto( $this->Game );
        
        $action = new GameCreatedActionDto();
        $action->game       = $gameDto;
        
        $action->myColor    = PlayerColor::Black;
        $this->Send( $this->Client1, $action );
        
        $action->myColor = PlayerColor::White;
        $this->Send( $this->Client2, $action );
        
        //$game->PlayState = GameState::OpponentConnectWaiting;
        $this->Game->PlayState = GameState::FirstThrow;
        // todo: visa på clienten även när det blir samma
        
        while ( $this->Game->PlayState == GameState::FirstThrow ) {
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
                
            $this->Send( $this->Client1, $rollAction );
            $this->Send( $this->Client2, $rollAction );
        }
        
        /* Create This on Frontend
         * =========================
         * https://stackoverflow.com/questions/33185302/how-to-make-a-php-function-loop-every-5-seconds
         */
        /*
        $this->moveTimeOut = new CancellationTokenSource();
        Utils::RepeatEvery( 500, () =>
        {
            TimeTick();
        }, $this->moveTimeOut );
        */
    }
    
    public function StartGamePlay(): void
    {
        $action = new GamePlayStartedActionDto();
        if ( $this->Client1 && ! $this->Game->BlackPlayer->IsAi() ) {
            $this->Send( $this->Client1, $action );
        }
        
        if ( $this->Client2 && ! $this->Game->WhitePlayer->IsAi() ) {
            $this->Send( $this->Client2, $action );
        }
    }
    
    protected function TimeTick(): void
    {
        if ( ! $this->moveTimeOut->IsCancellationRequested ) {
            $ellapsed = ( new \DateTime( 'now' ) ) - $this->Game->ThinkStart;
            if ( $ellapsed->TotalSeconds > $this->Game->TotalThinkTime ) {
                $this->log( "MyDebug: The time run out for {$this->Game->CurrentPlayer}" );
                $this->moveTimeOut->cancel();
                $winner = $this->Game->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                $this->EndGame( $winner );
            }
        }
    }
    
    protected function EndGame( PlayerColor $winner )
    {
        //$this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::Ended;
        $this->Log( "The winner is {$winner->value}" );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner, $newScore );
        //$this->eventDispatcher->dispatch( new GameEndedEvent( Mapper::GameToDto( $this->Game ) ), GameEndedEvent::NAME );
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
        
        if ( $this->Client1 && ! $this->Game->BlackPlayer->IsAi() ) {
            $this->Send( $this->Client1, $rollAction );
        }
        
        if ( $this->Client2 && ! $this->Game->WhitePlayer->IsAi() ) {
            $this->Send( $this->Client2, $rollAction );
        }
    }
    
    protected function IsAi( ?string $guid ): bool
    {
        return $guid == GamePlayer::AiUser;
    }
    
    protected function CreateDbGame(): void
    {
        $blackUser = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        
        if ( $this->Game->IsGoldGame && $blackUser->getGold() < self::firstBet ) {
           //throw new \Exception( "Black player dont have enough gold" ); // Should be guarder earlier
        }
            
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $blackUser->getGuid() ) ) {
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
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $whiteUser->getGuid() ) ) {
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
        $game->setGuid( $this->Game->Id );
        
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
            $this->log( "MyDebug: Stake" . $stake );
            $this->log( "MyDebug Initial gold: {$black->getGold()} {$this->Game->BlackPlayer->Gold} {$white->getGold()} {$this->Game->WhitePlayer->Gold}" );
            
            if ( $color == PlayerColor::Black ) {
                if ( ! $this->IsAi( $black->getGuid() ) )
                    $black->addGold( $stake );
                    $this->Game->BlackPlayer->Gold += stake;
            } else {
                if ( ! $this->IsAi( $white->getGuid() ) )
                    $white->addGold( $stake );
                    $this->Game->WhitePlayer->Gold += stake;
            }
            $this->log( "MyDebug After transfer: {$black->Gold} {$this->Game->BlackPlayer->Gold} {$white->Gold} {$this->Game->WhitePlayer->Gold}" );
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
    
    protected function DoMoves( MovesMadeActionDto $action ): void
    {
        if ( empty( $action->moves ) ) {
            return;
        }
        
        $firstMove = Mapper::MoveToMove( $action->moves[0], $this->Game );
        $validMove = $this->Game->ValidMoves->filter(
            function( $entry ) use ( $firstMove ) {
                return $entry == $firstMove;
            }
        );
        
        foreach ( $action->moves as $key => $move ) {
            if ( $validMove == null ) {
                // Preventing invalid moves to enter the state. Should not happen unless someones hacking the socket or serious bugs.
                throw new \RuntimeException( "An attempt to make an invalid move was made" );
            } else if ( $key !== \array_key_last( $action->moves ) ) {
                $nextMove = Mapper::MoveToMove( $action->moves[$key + 1], $this->Game );
                // Going up the valid moves tree one step for every sent move.
                $validMove = $validMove->NextMoves->filter(
                    function( $entry ) use ( $nextMove ) {
                        return $entry == $nextMove;
                    }
                );
            }
            
            $color = $action->moves->color;
            $move = Mapper::MoveToMove( $move, $this->Game );
            $this->Game->MakeMove( $move );
        }
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
        
        if ( ! $this->IsAi( $black->getGuid() ) ) {
            $black->Gold += $this->Game->Stake / 2;
            $em->persist( $black );
        }
        
        if ( ! $this->IsAi( $white->getGuid() ) ) {
            $white->Gold += $this->Game->Stake / 2;
            $em->persist( $white );
        }
            
        $em->push();
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket )
    {
        if ( $socket != null ) {
            $this->log( "MyDebug: Closing client" );
            //await socket.CloseAsync(WebSocketCloseStatus.NormalClosure, "Game aborted by client", CancellationToken.None);
            
            $socket->close( Frame::CLOSE_NORMAL );
            //$socket->close( Frame::CLOSE_GOING_AWAY );
        }
    }
    
    protected function Resign( PlayerColor $winner ): void
    {
        $this->EndGame( $winner );
        $this->log( "MyDebug: {$winner} won Game {$this->Game->Id} by resignition.");
    }
    
    protected function GetHintAction(): HintMovesActionDto
    {
        $moves              = $this->Engine->GetBestMoves();
        $hintMovesAction    = new HintMovesActionDto();
        
        $hintMovesAction->moves = $moves->map(
            function( $entry ) {
                $entry->hint = true;
                return $entry;
            }
        );
        
        return $hintMovesAction;
    }
    
    protected function DoDoubling(): void
    {
        $this->Game->GoldMultiplier *= 2;
        $this->Game->BlackPlayer->Gold -= $this->Game->Stake / 2;
        $this->Game->WhitePlayer->Gold -= $this->Game->Stake / 2;
        
        if ( $this->Game->WhitePlayer->Gold < 0 || $this->Game->BlackPlayer->Gold < 0 ) {
            throw new \RuntimeException( "Player out of gold. Should not be allowd." );
        }
        
        $blackUser = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        $whiteUser = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
        
        $em = $this->doctrine->getManager();
        if ( ! $this->IsAi( $blackUser->getGuid() ) ) { // gold for ai remains in the db
            $blackUser->setGold( $this->Game->BlackPlayer->Gold ); // non gold games guarded earlier in block.
            $em->persists( $blackUser );
        }
        
        if ( ! $this->IsAi( $whiteUser->getGuid() ) ) {
            $whiteUser->setGold( $this->Game->WhitePlayer->Gold );
            $em->persists( $whiteUser );
        }
        $em->flush();
        
        $this->Game->Stake += $this->Game->Stake;
        $this->Game->LastDoubler = $this->Game->CurrentPlayer;
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
                $this->log( "MyDebug: NewTurn for AI" );
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
        $action = new RolledActionDto();
        $this->Send( $client, $action );
        
        $moves = $this->Engine->GetBestMoves();
        $this->log( 'MyDebug EnginMoves: ' . print_r( $moves, true ) );
        
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
    
    protected function log( $logData ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( $logData );
        }
    }
}
