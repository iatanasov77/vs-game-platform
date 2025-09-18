<?php namespace App\Component\Manager\Games;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Amp\DeferredCancellation;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Manager\CardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;

use App\Component\Manager\CardGame\RoundResult;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Player;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\Utils\Guid;
use App\Component\Utils\HumanName;
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
use App\Component\Dto\Actions\BiddingStartedActionDto;

class BridgeBeloteGameManager extends CardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerPosition::South ) {
            $this->Clients->set( PlayerPosition::South->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->Players[PlayerPosition::South->value] );
            if ( $this->Game->IsGoldGame ) {
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $this->logger->log( "Play AI is TRUE !!!", 'GameManager' );
                
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                $this->InitializePlayer( $aiUser, true, $this->Game->Players[PlayerPosition::East->value] );
                $this->InitializePlayer( $aiUser, true, $this->Game->Players[PlayerPosition::North->value] );
                $this->InitializePlayer( $aiUser, true, $this->Game->Players[PlayerPosition::West->value] );
                
                $this->Engine = AiEngineFactory::CreateAiEngine(
                    $this->GameCode,
                    $this->GameVariant,
                    $this->logger,
                    $this->Game
                );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer != PlayerPosition::South ) {
                    $promise = \React\Async\async( function () {
                        $this->logger->log( "GameManager CurrentPlayer: Computer", 'GameManager' );
                        $this->EnginMoves( $this->Clients->get( PlayerPosition::South->value ) );
                    })();
                    \React\Async\await( $promise );
                }
            }
        } else if( $this->Game->CurrentPlayer == PlayerPosition::East ) {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as north. This is not expected" );
            }
            
            // East Player
            $this->Clients->set( PlayerPosition::East->value, $webSocket );
            $this->InitializePlayer( $dbUser, false, $this->Game->EastPlayer );
            
        } else if( $this->Game->CurrentPlayer == PlayerPosition::North ) {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as north. This is not expected" );
            }
            
            // South Player
            $this->Clients->set( PlayerPosition::North->value, $webSocket );
            $this->InitializePlayer( $dbUser, false, $this->Game->NorthPlayer );
            
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as north. This is not expected" );
            }
            
            // West Player
            $this->Clients->set( PlayerPosition::West->value, $webSocket );
            $this->InitializePlayer( $dbUser, false, $this->Game->WestPlayer );
            
            $this->CreateDbGame();
            $this->StartGame();
            
            //$this->dispatchGameEnded();
        }
    }
    
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        $position = PlayerPosition::from( $playerPositionId );
        
        $gameDto = Mapper::CardGameToDto( $this->Game );
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
        
        $gameDto = Mapper::CardGameToDto( $this->Game );
        $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
        $action = new GameCreatedActionDto();
        $action->game = $gameDto;
        
        $action->myPosition = PlayerPosition::South;
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        
        $action->myPosition = PlayerPosition::East;
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        
        $action->myPosition = PlayerPosition::North;
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        
        $action->myPosition = PlayerPosition::West;
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
        
        $this->Game->PlayState = GameState::firstBid;
        
        while ( $this->Game->PlayState == GameState::firstBid ) {
            $this->logger->log( 'First Throw State !!!', 'FirstThrowState' );
            
            $this->PlayRound();
            
            $biddingStartedAction = new BiddingStartedActionDto();
//             $biddingStartedAction->bids = $this->Game->Roll->map(
//                 function( $entry ) {
//                     return Mapper::DiceToDto( $entry );
//                 }
//             )->toArray();
            $biddingStartedAction->playerToBid = $this->Game->CurrentPlayer;
            $biddingStartedAction->moveTimer = Game::ClientCountDown;
            
            $this->Send( $this->Clients->get( PlayerPosition::South->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::East->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::North->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::West->value ), $biddingStartedAction );
        }
    }
    
    protected function CreateDbGame(): void
    {
        $southPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::South->value]->Id, PlayerPosition::South->value );
        $eastPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::East->value]->Id, PlayerPosition::East->value );
        $northPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::North->value]->Id, PlayerPosition::North->value );
        $westPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::West->value]->Id, PlayerPosition::West->value );
        
        // Create Game Session
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( $this->Game->Id );
        
        $southPlayer->setGame( $game );
        $eastPlayer->setGame( $game );
        $northPlayer->setGame( $game );
        $westPlayer->setGame( $game );
        
        $game->addGamePlayer( $southPlayer );
        $game->addGamePlayer( $eastPlayer );
        $game->addGamePlayer( $northPlayer );
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
        return;
        
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
    
    private function InitializePlayer( GamePlayer $dbUser, bool $aiUser, Player &$player ): void
    {
        if ( $aiUser ) {
            $playerName = HumanName::generate();
        } else {
            $playerName = $dbUser != null ? $dbUser->getName() : "Guest";
        }
        
        $player->Id = $dbUser != null ? $dbUser->getId() : 0;
        $player->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
        $player->Name = $playerName;
        $player->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
        $player->Elo = $dbUser != null ? $dbUser->getElo() : 0;
        
        if ( $this->Game->IsGoldGame ) {
            $player->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
        }
    }
}
