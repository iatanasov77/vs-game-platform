<?php namespace App\Component\Manager\Games;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;

use App\Component\Manager\BoardGameManager;
use App\Component\Rules\BoardGame\Game;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Entity\GamePlayer;

// Types
use App\Component\GamePlatform;
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\RolledActionDto;
use App\Component\Dto\Actions\HintMovesActionDto;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\Actions\UndoActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;
use App\Component\Dto\Actions\DoublingActionDto;

use App\EventListener\Event\GameStartedEvent;

final class BackgammonGameManager extends BoardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
            $this->logger->log( "Connecting Black Player ...", 'GameManager' );
            $this->Clients->set( PlayerColor::Black->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->Players[PlayerColor::Black->value] );
            if ( $this->Game->IsGoldGame ) {
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $this->logger->log( "Play AI is TRUE !!!", 'GameManager' );
                
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                $this->InitializePlayer( $aiUser, true, $this->Game->Players[PlayerColor::White->value] );
                
                $this->Engine = AiEngineFactory::CreateAiEngine(
                    $this->GameCode,
                    $this->GameVariant,
                    $this->logger,
                    $this->Game
                );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::White ) {
                    $this->setReactConnectAiOpponent( $webSocket );
                }
            }
        } else {
            $this->logger->log( "Connecting White Player ...", 'GameManager' );
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            $this->Clients->set( PlayerColor::White->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->Players[PlayerColor::White->value] );
            
            $this->CreateDbGame();
            $this->StartGame();
            
            // $this->eventDispatcher->dispatch( new GameEndedEvent( $this ), GameEndedEvent::NAME );
        }
    }
    
    public function StartGame(): void
    {
        $this->eventDispatcher->dispatch( new GameStartedEvent( $this ), GameStartedEvent::NAME );
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $this->SetDbGameStatus( GamePlatform::GAME_ROOM_STATUS_PLAYING );
        
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
            
            //$this->logger->log( 'First Throw Valid Moves: ' . \print_r( $rollAction->validMoves, true ), 'FirstThrowState' );
            //$this->logger->debug( $rollAction, 'FirstRoll.txt' );
            
            $this->Send( $this->Clients->get( PlayerColor::Black->value ), $rollAction );
            $this->Send( $this->Clients->get( PlayerColor::White->value ), $rollAction );
        }
        
        $this->setReactMoveTimeOut();
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
                $this->Game->Players[PlayerColor::Black->value]->FirstMoveMade = true;
            } else {
                $this->Game->Players[PlayerColor::White->value]->FirstMoveMade = true;
            }
            
            $this->DoMoves( $action );
            $this->setReactNewTurn( $socket );
            
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
        } else if ( $actionName == ActionNames::rolled ) {
            $action = $this->serializer->deserialize( $actionText, ActionDto::class, JsonEncoder::FORMAT );
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
            
            $otherSocket = $this->Game->OtherPlayer();
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
            $winner = \in_array( $this->Clients->get( PlayerColor::Black->value ), $otherSockets ) ? PlayerColor::Black : PlayerColor::White;
            $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            $this->logger->log( 'exitGame action recieved from GameManager.', 'GameManager' );
            
            $this->CloseConnections( $socket );
        }
        
        //$this->logger->debug( $this->Game->Points, 'AfterDoAction.txt' );
    }
    
    protected function NewTurn( WebsocketClientInterface $socket ): void
    {
        $winner = $this->GetWinner();
        $this->Game->SwitchPlayer();
        if ( $winner ) {
            $this->EndGame( $winner );
        } else {
            $this->SendNewRoll();
            
            if ( $this->AisTurn() ) {
                $this->logger->log( "NewTurn for AI", 'SwitchPlayer' );
                $this->EnginMoves( $socket );
            }
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
                $this->Game->GetHome( PlayerColor::White )->Checkers->filter(
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
    
    protected function GetHintAction(): HintMovesActionDto
    {
        $moves              = $this->Engine->GetBestMoves();
        $hintMovesAction    = new HintMovesActionDto();
        
        $hintMovesAction->moves = $moves->map(
            function( $entry ) {
                $moveDto = Mapper::MoveToDto( $entry );
                $moveDto->hint = true;
                
                return $moveDto;
            }
            )->toArray();
            
            return $hintMovesAction;
    }
    
    protected function DoDoubling(): void
    {
        $this->Game->GoldMultiplier *= 2;
        $this->Game->Players[PlayerColor::Black->value]->Gold -= $this->Game->Stake / 2;
        $this->Game->Players[PlayerColor::White->value]->Gold -= $this->Game->Stake / 2;
        
        if ( $this->Game->Players[PlayerColor::White->value]->Gold < 0 || $this->Game->Players[PlayerColor::Black->value]->Gold < 0 ) {
            throw new \RuntimeException( "Player out of gold. Should not be allowd." );
        }
        
        $blackUser = $this->playersRepository->find( $this->Game->Players[PlayerColor::Black->value]->Id );
        $whiteUser = $this->playersRepository->find( $this->Game->Players[PlayerColor::White->value]->Id );
        
        $em = $this->doctrine->getManager();
        if ( ! $this->IsAi( $blackUser->getGuid() ) ) { // gold for ai remains in the db
            $blackUser->setGold( $this->Game->Players[PlayerColor::Black->value]->Gold ); // non gold games guarded earlier in block.
            $em->persist( $blackUser );
        }
        
        if ( ! $this->IsAi( $whiteUser->getGuid() ) ) {
            $whiteUser->setGold( $this->Game->Players[PlayerColor::White->value]->Gold );
            $em->persist( $whiteUser );
        }
        $em->flush();
        
        $this->Game->Stake += $this->Game->Stake;
        $this->Game->LastDoubler = $this->Game->CurrentPlayer;
    }
    
    protected function SendNewRoll(): void
    {
        $this->Game->RollDice();
        $this->logger->log( "NewRoll: " . \print_r( $this->Game->Roll, true ), 'NewRoll' );
        
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
        
        if ( $this->Clients->get( PlayerColor::Black->value ) && ! $this->Game->Players[PlayerColor::Black->value]->IsAi() ) {
            $this->logger->log( "Sending NewRoll to Client1 !!!", 'NewRoll' );
            $this->Send( $this->Clients->get( PlayerColor::Black->value ), $rollAction );
        }
        
        if ( $this->Clients->get( PlayerColor::White->value ) && ! $this->Game->Players[PlayerColor::White->value]->IsAi() ) {
            $this->logger->log( "Sending NewRoll to Client2 !!!", 'NewRoll' );
            $this->Send( $this->Clients->get( PlayerColor::White->value ), $rollAction );
        }
    }
    
    protected function DoMoves( MovesMadeActionDto $action ): void
    {
        if ( empty( $action->moves ) ) {
            return;
        }
        
        //$this->logger->debug( $action->moves[0], 'MoveDto.txt' );
        $firstMove = Mapper::MoveToMove( $action->moves[0], $this->Game );
        $validMove = $this->Game->ValidMoves->filter(
            function( $entry ) use ( $firstMove ) {
                //return $entry == $firstMove;
                return
                    $entry->From->GetNumber( $firstMove->Color ) == $firstMove->From->GetNumber( $firstMove->Color ) &&
                    $entry->To->GetNumber( $firstMove->Color ) == $firstMove->To->GetNumber( $firstMove->Color )
                ;
            }
        )->first();
        
        //$this->logger->log( \print_r( $firstMove, true ), 'DoMoves' );
        //$this->logger->log( \print_r( $this->Game->ValidMoves, true ), 'DoMoves' );
        //$this->logger->debug( $firstMove, 'DoMoves_FirstMove.txt' );
        //$this->logger->debug( $this->Game->ValidMoves, 'GameValidMoves.txt' );
        //$this->debugGetCheckerFromPoint();
        
        $this->logger->log( "Points Before DoMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'DoMoves' );
        for ( $i = 0; $i < count( $action->moves ); $i++ ) {
            $moveDto = $action->moves[$i];
            if ( $validMove == null ) {
                // Preventing invalid moves to enter the state. Should not happen unless someones hacking the socket or serious bugs.
                throw new \RuntimeException( "An attempt to make an invalid move was made" );
            } else if ( $i < count( $action->moves ) - 1 ) {
                $nextMove = Mapper::MoveToMove( $action->moves[$i + 1], $this->Game );
                
                // Going up the valid moves tree one step for every sent move.
                $validMove = $validMove->NextMoves->filter(
                    function( $entry ) use ( $nextMove ) {
                        //return $entry == $nextMove;
                        return
                            $entry->From->GetNumber( $nextMove->Color ) == $nextMove->From->GetNumber( $nextMove->Color ) &&
                            $entry->To->GetNumber( $nextMove->Color ) == $nextMove->To->GetNumber( $nextMove->Color )
                        ;
                    }
                )->first();
            }
            
            //$color  = $move->color;
            $move   = Mapper::MoveToMove( $moveDto, $this->Game );
            $this->Game->MakeMove( $move );
        }
        $this->logger->log( "Points After DoMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'DoMoves' );
        //$this->logger->log( "Black Player Points Left: " . $this->Game->BlackPlayer->PointsLeft, 'EndGame' );
    }
    
    protected function sendRolledAction( WebsocketClientInterface $client ): void
    {
        $sleepMileseconds   = \rand( 700, 1200 );
        $this->ReactAsyncDelay( $sleepMileseconds / 1000 );
        
        $action = new RolledActionDto();
        $this->Send( $client, $action );
    }
    
    protected function sendEngineMove( WebsocketClientInterface $client, $move, &$noMoves ): void
    {
        $sleepMileseconds   = \rand( 700, 1200 );
        $this->ReactAsyncDelay( $sleepMileseconds / 1000 );
        
        $moveDto = Mapper::MoveToDto( $move );
        $moveDto->animate = true;
        $dto = new OpponentMoveActionDto();
        $dto->move = $moveDto;
        
        $hit = $this->Game->MakeMove( $move );
        if ( $hit ) {
            $this->logger->log( "Has a Hit at BlackNumber Point: " . $move->To->BlackNumber, 'EnginMoves' );
        }
        
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
            $this->Game->Players[PlayerColor::Black->value]->FirstMoveMade = true;
        } else {
            $this->Game->Players[PlayerColor::White->value]->FirstMoveMade = true;
        }
        
        $noMoves = false;
        $this->Send( $client, $dto );
    }
    
    protected function EnginMoves( WebsocketClientInterface $client ): void
    {
        $this->setEngineRolledAction( $client );
        
        $moves = $this->Engine->GetBestMoves();
        //$this->logger->log( print_r( $moves->toArray(), true ), 'EnginMoves' );
        
        $noMoves = true;
        $this->logger->log( "Points Before EnginMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'EnginMoves' );
        for ( $i = 0; $i < $moves->count(); $i++ ) {
            $move = $moves[$i];
            if ( $move->isNull() ) {
                continue;
            }
            
            $this->setEngineMove( $client, $move, $noMoves );
        }
        $this->logger->log( "Points After EnginMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'EnginMoves' );
        
        if ( $noMoves ) {
            $this->ReactAsyncDelayAwait( 2.5 );
        }
        
        $this->setReactNewTurn( $client );
    }
    
    protected function SendWinner( PlayerColor $color, ?array $newScore ): void
    {
        $game = Mapper::BoardGameToDto( $this->Game );
        $game->winner = $color;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        $gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerColor::Black->value ), $gameEndedAction );
        
        $gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerColor::White->value ), $gameEndedAction );
    }
    
    protected function debugGetCheckerFromPoint()
    {
        //$this->logger->debug( $this->Game->Points, 'GamePoints.txt' );
        
        $checkerFromPoint = $this->Game->Points->filter(
            function( $entry ) {
                return $entry->GetNumber( PlayerColor::Black ) == 1;
            }
            )->first();
            //$this->logger->debug( $checkerFromPoint, 'CheckerFromPoint.txt' );
    }
}
