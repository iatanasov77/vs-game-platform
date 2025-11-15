<?php namespace App\Component\Manager\Games;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Amp\DeferredCancellation;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Manager\BoardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\BoardGame\Game;
use App\Component\Rules\BoardGame\Player;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\Utils\Guid;
use App\Component\Websocket\WebSocketState;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\HintMovesActionDto;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\Actions\UndoActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;
use App\Component\Dto\Actions\DoublingActionDto;

final class ChessGameManager extends BoardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
            $this->logger->log( "Connecting Black Player ...", 'GameManager' );
            $this->Clients->set( PlayerColor::Black->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->BlackPlayer );
            if ( $this->Game->IsGoldGame ) {
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $this->logger->log( "Play AI is TRUE !!!", 'GameManager' );
                
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                $this->InitializePlayer( $aiUser, true, $this->Game->WhitePlayer );
                
                $this->Engine = AiEngineFactory::CreateAiEngine(
                    $this->GameCode,
                    $this->GameVariant,
                    $this->logger,
                    $this->Game
                    );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::White ) {
                    $promise = Async\async( function () {
                        $this->logger->log( "GameManager CurrentPlayer: White", 'GameManager' );
                        $this->EnginMoves( $this->Clients->get( PlayerColor::Black->value ) );
                    })();
                    \React\Async\await( $promise );
                }
            }
        } else {
            $this->logger->log( "Connecting White Player ...", 'GameManager' );
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            $this->Clients->set( PlayerColor::White->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->WhitePlayer );
            
            $this->CreateDbGame();
            $this->StartGame();
            
            //$this->dispatchGameEnded();
        }
    }
    
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        $color = PlayerColor::from( $playerPositionId );
        
        $gameDto = Mapper::BoardGameToDto( $this->Game );
        $restoreAction = new GameRestoreActionDto();
        $restoreAction->game = $gameDto;
        $restoreAction->color = $color;
            
        if ( $color == PlayerColor::Black ) {
            $this->Clients->set( PlayerColor::Black->value, $socket );
            $otherSocket = $this->Clients->get( PlayerColor::White->value );
        } else {
            $this->Clients->set( PlayerColor::White->value, $socket );
            $otherSocket = $this->Clients->get( PlayerColor::Black->value );
        }
        
        $this->Send( $socket, $restoreAction );
        //Also send the state to the other client in case it has made moves.
        if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
            $restoreAction->color = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
            $this->Send( $otherSocket, $restoreAction );
        } else {
            $this->logger->log( "Failed to send restore to other client", 'GameManager' );
        }
    }
    
    public function StartGame(): void
    {
        $this->Game->ThinkStart = new \DateTime( 'now' );
        
        $gameDto = Mapper::BoardGameToDto( $this->Game );
        // $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
        $action = new GameCreatedActionDto();
        $action->game = $gameDto;
        
        $action->myColor = PlayerColor::Black;
        $this->Send( $this->Clients->get( PlayerColor::Black->value ), $action );
        
        $action->myColor = PlayerColor::White;
        $this->Send( $this->Clients->get( PlayerColor::White->value ), $action );
        
        $this->Game->PlayState = GameState::firstThrow;
        
        // todo: visa på clienten även när det blir samma
        // English: visa for clients who are not allowed to contact them
        while ( $this->Game->PlayState == GameState::firstThrow ) {
            $this->logger->log( 'First Throw State !!!', 'FirstThrowState' );
            
        }
        
        $this->moveTimeOut = new DeferredCancellation();
        if ( $this->EndGameOnTotalThinkTimeElapse ) {
            Async\async( function () {
                $loop = Loop::get();
                $loop->addPeriodicTimer( 0.5, function ( TimerInterface $timer ) use ( $loop ) {
                    if ( ! $this->moveTimeOut->isCancelled() ) {
                        $this->TimeTick();
                    } else {
                        $loop->cancelTimer( $timer );
                    }
                });
            })();
        }
    }
    
    public function DoAction(
        ActionNames $actionName,
        string $actionText,
        WebsocketClientInterface $socket,
        //?WebsocketClientInterface $otherSocket
        array $otherSockets
    ): void {
        $this->logger->log( "Doing action: {$actionName->value}", 'GameManager' );
        //$this->logger->debug( $this->Game->Points, 'BeforeDoAction.txt' );
        
        if ( $actionName == ActionNames::movesMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, MovesMadeActionDto::class, JsonEncoder::FORMAT );
            
            if ( $socket == $this->Clients->get( PlayerColor::Black->value ) ) {
                $this->Game->BlackPlayer->FirstMoveMade = true;
            } else {
                $this->Game->WhitePlayer->FirstMoveMade = true;
            }
            
            $this->DoMoves( $action );
            $promise = Async\async( function () use ( $action, $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
            
        } else if ( $actionName == ActionNames::opponentMove ) {
            $action = $this->serializer->deserialize( $actionText, OpponentMoveActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::undoMove ) {
            $action = $this->serializer->deserialize( $actionText, UndoActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::requestedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \RuntimeException( "requestedDoubling should not be possible in a non gold game" );
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
                foreach ( $otherSockets as $otherSocket ) {
                    $this->Send( $otherSocket, $action );
                }
            }
        } else if ( $actionName == ActionNames::acceptedDoubling ) {
            if ( ! $this->Game->IsGoldGame ) {
                throw new \RuntimeException( "acceptedDoubling should not be possible in a non gold game" );
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
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::resign ) {
            $winner = $this->Clients->get( PlayerColor::Black->value ) == $otherSocket ? PlayerColor::Black : PlayerColor::White;
            $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            $this->logger->log( 'exitGame action recieved from GameManager.', 'GameManager' );
            $this->CloseConnections( $socket );
        }
        
        //$this->logger->debug( $this->Game->Points, 'AfterDoAction.txt' );
    }
}
