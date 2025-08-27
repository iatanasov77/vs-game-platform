<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\CardGame\Game;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\System\Guid;
use App\Component\Websocket\WebSocketState;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

// Types
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\OpponentMoveActionDto;
use App\Component\Dto\Actions\RolledActionDto;

class BridgeBeloteGameManager extends AbstractGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerPosition::North ) {
            $this->Clients->set( PlayerPosition::North->value, $webSocket );
            
            $this->Game->NorthPlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->NorthPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->NorthPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->NorthPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->NorthPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            
            if ( $this->Game->IsGoldGame ) {
                $this->Game->NorthPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $this->logger->log( "Play AI is TRUE !!!", 'GameManager' );
                
                /*
                 $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                 
                 $this->Game->WhitePlayer->Id = $aiUser->getId();
                 $this->Game->WhitePlayer->Guid = $aiUser->getGuid();
                 $this->Game->WhitePlayer->Name = $aiUser->getName();
                 $this->Game->WhitePlayer->Photo = $aiUser->getPhotoUrl();
                 $this->Game->WhitePlayer->Elo = $aiUser->getElo();
                 
                 if ( $this->Game->IsGoldGame ) {
                 $this->Game->WhitePlayer->Gold = $aiUser->getGold();
                 }
                 */
                
                $this->Engine = AiEngineFactory::CreateAiEngine(
                    $this->GameCode,
                    $this->GameVariant,
                    $this->logger,
                    $this->Game
                );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer != PlayerPosition::North ) {
                    $promise = \React\Async\async( function () {
                        $this->logger->log( "GameManager CurrentPlayer: White", 'GameManager' );
                        $this->EnginMoves( $this->Clients->get( PlayerPosition::North->value ) );
                    })();
                    \React\Async\await( $promise );
                }
            }
        } else if( $position == PlayerPosition::East ) {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            
            // East Player
            $this->Clients->set( PlayerPosition::East->value, $webSocket );
            $this->Game->EastPlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->EastPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->EastPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->EastPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->EastPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->EastPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
            }
            
        } else if( $position == PlayerPosition::South ) {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            
            // South Player
            $this->Clients->set( PlayerPosition::South->value, $webSocket );
            $this->Game->SouthPlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->SouthPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->SouthPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->SouthPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->SouthPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->SouthPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
            }
            
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            
            // West Player
            $this->Clients->set( PlayerPosition::West->value, $webSocket );
            $this->Game->WestPlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->WestPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->WestPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->WestPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->WestPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->WestPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
            }
            
            $this->CreateDbGame();
            $this->StartGame();
            
            //$this->dispatchGameEnded();
        }
    }
    
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        $position = PlayerPosition::from( $playerPositionId );
        
        $gameDto = Mapper::GameToDto( $this->Game );
        $restoreAction = new GameRestoreActionDto();
        $restoreAction->game = $gameDto;
        $restoreAction->position = $position;
//         $restoreAction->dices = $this->Game->Roll->map(
//             function( $entry ) {
//                 return Mapper::DiceToDto( $entry );
//             }
//         )->toArray();
        
        
        $this->Clients->set( $position->value, $socket );
        $otherSockets = [];
        foreach ( $this->Clients->toArray() as $key => $client ) {
            if ( $key !== $position->value ) {
                $otherSockets[$key] = $client;
            }
        }
        
        $this->Send( $socket, $restoreAction );
        
        //Also send the state to the other clients in case it has made moves.
        foreach ( $otherSockets as $key => $otherSocket ) {
            if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
                $restoreAction->position = PlayerPosition::from( $key );
                $this->Send( $otherSocket, $restoreAction );
            }
        }
    }
    
    public function StartGame(): void
    {
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $gameDto = Mapper::GameToDto( $this->Game );
        $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
        $action = new GameCreatedActionDto();
        $action->game = $gameDto;
        
        $action->myColor = PlayerPosition::North;
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        
        $action->myColor = PlayerPosition::East;
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        
        $action->myColor = PlayerPosition::South;
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        
        $action->myColor = PlayerPosition::West;
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
        
        $this->Game->PlayState = GameState::firstAnnounce;
    }
    
    protected function CreateDbGame(): void
    {
        $northPlayer = $this->CreateTempPlayer( $this->Game->NorthPlayer->Id, PlayerPosition::North->value );
        $eastPlayer = $this->CreateTempPlayer( $this->Game->EastPlayer->Id, PlayerPosition::East->value );
        $southPlayer = $this->CreateTempPlayer( $this->Game->SouthPlayer->Id, PlayerPosition::South->value );
        $westPlayer = $this->CreateTempPlayer( $this->Game->WestPlayer->Id, PlayerPosition::West->value );
        
        // Create Game Session
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( $this->Game->Id );
        
        $northPlayer->setGame( $game );
        $eastPlayer->setGame( $game );
        $southPlayer->setGame( $game );
        $westPlayer->setGame( $game );
        
        $game->addGamePlayer( $northPlayer );
        $game->addGamePlayer( $eastPlayer );
        $game->addGamePlayer( $southPlayer );
        $game->addGamePlayer( $westPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
    }
    
    protected function IsAi( ?string $guid ): bool
    {
        return $guid == GamePlayer::AiUser;
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
        $tempPlayer->setPosition( $playerPositionId );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
}
