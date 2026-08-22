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

use App\Component\Rules\CardGame\Player;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\Utils\Guid;
use App\Component\Utils\HumanName;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

// Types
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;
use App\Component\Type\AnnounceType;
use App\Component\Type\GameState;
use App\Component\Type\CardGameTeam;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\OpponentBidsActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\OpponentPlayCardActionDto;
use App\Component\Dto\Actions\AnnounceMadeActionDto;

/**
 * ContractBridgeGame Engine in Phython: https://github.com/lorserker/ben
 * ContractBridgeGame in C#: https://github.com/PatrykkMar/Bridget
 * 
 * Use Conract Bridge Library: https://github.com/garak/bridge
 */
class ContractBridgeGameManager extends CardGameManager
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
        $this->Game->SwitchPlayer();
        
        // Check/Set Trick Winner
        if ( ! $this->ContinuePlay() ) {
            //return;
        }
        
        // Engine Bidding or Playing
        $this->PlayRound( $socket );
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
        if ( $tricksWinner ) {
            if ( $this->Game->trickNumber > 13 ) {
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
        
        if ( $this->Game->PlayState == GameState::firstRound ) {
            $this->StartGamePlay();
        }
        
        if ( $this->Game->PlayState == GameState::roundEnded ) {
            $this->EndRound();
        }
        
        return true;
    }
    
    protected function DoBid( BidMadeActionDto $action ): void
    {
        $this->logger->log( 'Contract Bridge DoBid !!!', 'GameManager' );
        
        $bid = new Bid( $action->bid->Player, BidTrump::fromValue( $action->bid->Trump ) );
        $bid->KontraPlayer = $action->bid->KontraPlayer;
        $bid->ReKontraPlayer = $action->bid->ReKontraPlayer;
        
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
                $this->Game->CurrentContract->Trump,
                $this->Game->GetTrickActions(),
                $trickAction->Card
                );
            
            if ( $belote ) {
                $announce = new Announce( AnnounceType::Belot, $trickAction->Card );
                
                $announce->Player = $this->Game->CurrentPlayer;
                $this->Game->announces[] = $announce;
                
                $action = new AnnounceMadeActionDto();
                $action->announce = Mapper::AnnounceToDto( $announce, $this->Game->CurrentPlayer );
                
                $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
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
                    return Mapper::CardToDto( $entry, $this->Game->GameCode, $this->Game->CurrentPlayer );
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
                $this->Game->CurrentContract->Trump,
                $this->Game->GetTrickActions(),
                $playCardAction->Card
            );
            
            if ( $belote ) {
                $announce = new Announce( AnnounceType::Belot, $playCardAction->Card );
                
                $announce->Player = $this->Game->CurrentPlayer;
                $this->Game->announces[] = $announce;
                
                $action = new AnnounceMadeActionDto();
                $action->announce = Mapper::AnnounceToDto( $announce, $this->Game->CurrentPlayer );
                
                $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
                $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
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
            $action->Card = Mapper::CardToDto( $playCardAction->Card, $this->Game->GameCode, $playCardAction->Player );
            $action->Belote = $playCardAction->Belote;
            $action->Player = $playCardAction->Player;
            $action->TrickNumber = $playCardAction->TrickNumber;
            
            $action->validCards = $this->Game->ValidCards->map(
                function( $entry ) use ( $nextPlayer ) {
                    return Mapper::CardToDto( $entry, $this->Game->GameCode, $nextPlayer ); // PlayerPosition::South
                }
                )->getValues(); // ->toArray();
                $action->nextPlayer = $nextPlayer;
                
                $this->Send( $client, $action );
        })();
        Async\await( $promise );
    }
    
    protected function GetWinner(): ?CardGameTeam
    {
        $winner = null;
        
        if ( $this->Game->southNorthPoints >= 151 ) {
            $winner = CardGameTeam::SouthNorth;
        }
        
        if ( $this->Game->eastWestPoints >= 151 ) {
            $winner = CardGameTeam::EastWest;
        }
        
        return $winner;
    }
    
    protected function FirstToPlay(): PlayerPosition
    {
        switch ( $this->Game->CurrentContract->Player ) {
            case PlayerPosition::South:
                $firstToPlay = PlayerPosition::West;
                break;
            case PlayerPosition::East:
                $firstToPlay = PlayerPosition::South;
                break;
            case PlayerPosition::North:
                $firstToPlay = PlayerPosition::East;
                break;
            case PlayerPosition::West:
                $firstToPlay = PlayerPosition::North;
                break;
            default:
                throw new \RuntimeException( "Invalid player position." );
        }
        
        return $firstToPlay;
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
