<?php namespace App\Component\Manager\Games;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Amp\DeferredCancellation;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Manager\CardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;

use App\Component\Rules\CardGame\GameMechanics\RoundResult;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Player;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\Utils\Guid;
use App\Component\Utils\HumanName;
use App\Component\Websocket\WebSocketState;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

// Types
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Type\AnnounceType;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\OpponentBidsActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\OpponentPlayCardActionDto;

class BridgeBeloteGameManager extends CardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerPosition::South ) {
            $this->Clients->set( PlayerPosition::South->value, $webSocket );
            
            $this->InitializePlayer( $dbUser, false, $this->Game->Players[PlayerPosition::South->value] );
            
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
                        $this->EnginBids( $this->Clients->get( PlayerPosition::South->value ) );
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
    
    public function DoAction(
        ActionNames $actionName,
        string $actionText,
        WebsocketClientInterface $socket,
        //?WebsocketClientInterface $otherSocket
        array $otherSockets
    ): void {
        $this->logger->log( "Doing action: {$actionName->value}", 'GameManager' );
        //$this->logger->debug( $this->Game->Points, 'BeforeDoAction.txt' );
        
        if ( $actionName == ActionNames::bidMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, BidMadeActionDto::class, JsonEncoder::FORMAT );
            
            $this->DoBid( $action );
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        } else if ( $actionName == ActionNames::opponentBids ) {
            $action = $this->serializer->deserialize( $actionText, OpponentBidsActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::playCard ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, PlayCardActionDto::class, JsonEncoder::FORMAT );
            
            $this->PlayCard( $action );
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        } else if ( $actionName == ActionNames::opponentPlayCard ) {
            $action = $this->serializer->deserialize( $actionText, OpponentPlayCardActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::startNewRound ) {
            $this->logger->log( 'startNewRound action recieved from GameManager.', 'GameManager' );
            $this->StartNewRound();
            $this->PlayRound( $socket );
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
            if ( ! $this->ContinuePlay() ) {
                return;
            }
            
            $this->PlayRound( $socket );
        }
    }
    
    protected function AisTurn(): bool
    {
        switch ( $this->Game->CurrentPlayer ) {
            case PlayerPosition::South:
                $plyr = $this->Game->Players[PlayerPosition::South->value];
                break;
            case PlayerPosition::North:
                $plyr = $this->Game->Players[PlayerPosition::North->value];
                break;
            case PlayerPosition::West:
                $plyr = $this->Game->Players[PlayerPosition::West->value];
                break;
                break;
            case PlayerPosition::East:
                $plyr = $this->Game->Players[PlayerPosition::East->value];
                break;
            default:
                throw new \RuntimeException( 'Wrong Current Player !' );
        }
        
        $this->logger->log( "AisTurn CurrentPlayer: " . \print_r( $plyr, true ) , 'SwitchPlayer' );
        return $plyr->IsAi();
    }
    
    protected function ContinuePlay(): bool
    {
        $tricksWinner   = $this->Game->PlayRound();
        if ( $this->Game->PlayState == GameState::firstRound ) {
            $this->logger->log( 'Playing Card Game Round Started.', 'GameManager' );
            $this->StartGamePlay();
            return false;
        }
        
        if ( $tricksWinner ) {
            if ( $this->Game->trickNumber > 8 ) {
                $this->Game->roundNumber++;
                $this->Game->trickNumber = 1;
                $this->EndRound();
                return false;
            }
            
            //sleep( 10 );
            $this->SendTrickWinner( $tricksWinner );
            
            $this->logger->log( "Continue Play !!!", 'GameManager' );
            if ( $this->Game->PlayState != GameState::roundEnded && $this->AisTurn() ) {
                $socket = $this->Clients->first();
                $this->EnginPlayCard( $socket );
                
                $promise = Async\async( function () use ( $socket ) {
                    $this->NewTurn( $socket );
                })();
                Async\await( $promise );
            }
        }
        
        return true;
    }
    
    protected function DoBid( BidMadeActionDto $action ): void
    {
        $bid = new Bid( $action->bid->Player, BidType::fromValue( $action->bid->Type ) );
        
        $nextPlayer = $this->Game->NextPlayer();
        $this->Game->SetContract( $bid, $nextPlayer );
    }
    
    protected function PlayCard( PlayCardActionDto $action ): void
    {
        $playedCard = Card::GetCard( $action->Card->Suit, $action->Card->Type );
        $trickAction = new PlayCardAction( $playedCard, $this->Game->playerCards[$this->Game->CurrentPlayer->value]->count() > 1 );
        
        // Belote
        if ( $trickAction->Belote ) {
            $belote = $this->Game->IsBeloteAllowed(
                $this->Game->playerCards[$this->Game->CurrentPlayer->value],
                $this->Game->CurrentContract->Type,
                $this->Game->GetTrickActions(),
                $trickAction->Card
            );
            
            if ( $belote ) {
                $announce = new Announce( AnnounceType::Belot, $trickAction->Card );
                
                $announce->Player = $this->Game->CurrentPlayer;
                $this->Game->announces[] = $announce;
            }
        }
        
        // Update information after the action
        $this->Game->playerCards[$this->Game->CurrentPlayer->value]->removeElement( $trickAction->Card );
        $trickAction->Player = $this->Game->CurrentPlayer;
        $trickAction->TrickNumber = $this->Game->GetTrickActionNumber() + 1;
        
        $this->Game->AddTrickAction( $trickAction );
    }
    
    protected function EnginBids( WebsocketClientInterface $client ): void
    {
        // Debug Player Cards
        $playerCards = $this->Game->playerCards[$this->Game->CurrentPlayer->value];
        
        $bid = new Bid( $this->Game->CurrentPlayer, $this->Engine->DoBid() );
        
        $promise = Async\async( function () use ( $client, $bid, $playerCards ) {
            $sleepMileseconds   = \rand( 700, 1200 );
            Async\delay( $sleepMileseconds / 1000 );
            
            $nextPlayer = $this->Game->NextPlayer();
            $this->Game->SetContract( $bid, $nextPlayer );
            
            $action = new OpponentBidsActionDto();
            $action->bid = Mapper::BidToDto( $bid );
            
            $validBids = $this->Game->AvailableBids->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
            $action->validBids = \array_values( $validBids );
            
            $action->nextPlayer = $nextPlayer;
            $action->playState = $this->Game->PlayState;
            
            $action->MyCards = $playerCards->map(
                function( $entry ) {
                    return Mapper::CardToDto( $entry, $this->Game->CurrentPlayer );
                }
            );
            
            $this->Send( $client, $action );
        })();
        Async\await( $promise );
    }
    
    protected function EnginPlayCard( WebsocketClientInterface $client ): void
    {
        $playCardAction = $this->Engine->PlayCard();
        
        // Belote
        if ( $playCardAction->Belote ) {
            $belote = $this->Game->IsBeloteAllowed(
                $this->Game->playerCards[$this->Game->CurrentPlayer->value],
                $this->Game->CurrentContract->Type,
                $this->Game->GetTrickActions(),
                $playCardAction->Card
            );
            
            if ( $belote ) {
                $announce = new Announce( AnnounceType::Belot, $playCardAction->Card );
                
                $announce->Player = $this->Game->CurrentPlayer;
                $this->Game->announces[] = $announce;
            }
        }
        
        $promise = Async\async( function () use ( $client, $playCardAction ) {
            $sleepMileseconds   = \rand( 700, 1200 );
            Async\delay( $sleepMileseconds / 1000 );
            
            $nextPlayer = $this->Game->NextPlayer();
            $this->Game->AddTrickAction( $playCardAction );
            $this->Game->ValidCards = $this->Game->GetValidCards(
                $this->Game->playerCards[$nextPlayer->value],
                $this->Game->CurrentContract,
                $this->Game->GetTrickActions()
            );
            
            $action = new OpponentPlayCardActionDto();
            $action->Card = Mapper::CardToDto( $playCardAction->Card, $playCardAction->Player );
            $action->Belote = $playCardAction->Belote;
            $action->Player = $playCardAction->Player;
            $action->TrickNumber = $playCardAction->TrickNumber;
            
            $action->validCards = $this->Game->ValidCards->map(
                function( $entry ) use ( $nextPlayer ) {
                    return Mapper::CardToDto( $entry, $nextPlayer ); // PlayerPosition::South
                }
            )->getValues(); // ->toArray();
            $action->nextPlayer = $nextPlayer;
            
            $this->Send( $client, $action );
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
