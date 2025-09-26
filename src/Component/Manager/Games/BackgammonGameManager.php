<?php namespace App\Component\Manager\Games;

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
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\RolledActionDto;
use App\Component\Dto\Actions\HintMovesActionDto;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;

final class BackgammonGameManager extends BoardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
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
        $restoreAction->dices = $this->Game->Roll->map(
            function( $entry ) {
                return Mapper::DiceToDto( $entry );
            }
        )->toArray();
        
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
        $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
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
    
    protected function CreateDbGame(): void
    {
        $blackPlayer = $this->CreateTempPlayer( $this->Game->BlackPlayer->Id, PlayerColor::Black->value );
        $whitePlayer = $this->CreateTempPlayer( $this->Game->WhitePlayer->Id, PlayerColor::White->value );
        
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( $this->Game->Id );
        
        $blackPlayer->setGame( $game );
        $whitePlayer->setGame( $game );
        
        $game->addGamePlayer( $blackPlayer );
        $game->addGamePlayer( $whitePlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
    }
    
    protected function IsAi( ?string $guid ): bool
    {
        return $guid == GamePlayer::AiUser;
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
            $em->persist( $blackUser );
        }
        
        if ( ! $this->IsAi( $whiteUser->getGuid() ) ) {
            $whiteUser->setGold( $this->Game->WhitePlayer->Gold );
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
        
        if ( $this->Clients->get( PlayerColor::Black->value ) && ! $this->Game->BlackPlayer->IsAi() ) {
            $this->logger->log( "Sending NewRoll to Client1 !!!", 'NewRoll' );
            $this->Send( $this->Clients->get( PlayerColor::Black->value ), $rollAction );
        }
        
        if ( $this->Clients->get( PlayerColor::White->value ) && ! $this->Game->WhitePlayer->IsAi() ) {
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
    
    protected function AisTurn(): bool
    {
        $plyr = $this->Game->CurrentPlayer == PlayerColor::Black ? $this->Game->BlackPlayer : $this->Game->WhitePlayer;
        $this->logger->log( "AisTurn CurrentPlayer: " . \print_r( $plyr, true ) , 'SwitchPlayer' );
        
        return $plyr->IsAi();
    }
    
    protected function EnginMoves( WebsocketClientInterface $client )
    {
        $promise = Async\async( function () use ( $client ) {
            $sleepMileseconds   = \rand( 700, 1200 );
            Async\delay( $sleepMileseconds / 1000 );
            
            $action = new RolledActionDto();
            $this->Send( $client, $action );
        })();
        Async\await( $promise );
        
        $moves = $this->Engine->GetBestMoves();
        //$this->logger->log( print_r( $moves->toArray(), true ), 'EnginMoves' );
        
        $noMoves = true;
        $this->logger->log( "Points Before EnginMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'EnginMoves' );
        for ( $i = 0; $i < $moves->count(); $i++ ) {
            $move = $moves[$i];
            if ( $move->isNull() ) {
                continue;
            }
            
            $promise = Async\async( function () use ( $client, $move, &$noMoves ) {
                $sleepMileseconds   = \rand( 700, 1200 );
                Async\delay( $sleepMileseconds / 1000 );
                
                $moveDto = Mapper::MoveToDto( $move );
                $moveDto->animate = true;
                $dto = new OpponentMoveActionDto();
                $dto->move = $moveDto;
                
                $hit = $this->Game->MakeMove( $move );
                if ( $hit ) {
                    $this->logger->log( "Has a Hit at BlackNumber Point: " . $move->To->BlackNumber, 'EnginMoves' );
                }
                
                if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
                    $this->Game->BlackPlayer->FirstMoveMade = true;
                } else {
                    $this->Game->WhitePlayer->FirstMoveMade = true;
                }
                
                $noMoves = false;
                $this->Send( $client, $dto );
            })();
            Async\await( $promise );
        }
        $this->logger->log( "Points After EnginMoves: " . \print_r( $this->Game->Points->toArray(), true ), 'EnginMoves' );
        
        if ( $noMoves ) {
            $promise = Async\async( function () {
                Async\delay( 2.5 );
            })();
            Async\await( $promise );
        }
        
        $promise = Async\async( function () use ( $client ) {
            $this->NewTurn( $client );
        })();
        Async\await( $promise );
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
    
    private function CreateTempPlayer( int $playerId, int $playerPositionId ): TempPlayer
    {
        $player = $this->playersRepository->find( $playerId );
        
        if ( $this->Game->IsGoldGame && $player->getGold() < self::firstBet ) {
            throw new \RuntimeException( "Black player dont have enough gold" ); // Should be guarder earlier
        }
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $player->getGuid() ) ) {
            $player->setGold( self::firstBet );
        }
        
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setColor( $playerPositionId );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
    
    private function InitializePlayer( GamePlayer $dbUser, bool $aiUser, Player &$player ): void
    {
        $player->Id = $dbUser != null ? $dbUser->getId() : 0;
        $player->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
        $player->Name = $dbUser != null ? $dbUser->getName() : "Guest";
        $player->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
        $player->Elo = $dbUser != null ? $dbUser->getElo() : 0;
        
        if ( $this->Game->IsGoldGame ) {
            $player->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
        }
    }
}
