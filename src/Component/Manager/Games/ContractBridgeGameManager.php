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
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Entity\GamePlayer;

// Types
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;
use App\Component\Type\AnnounceType;
use App\Component\Type\GameState;
use App\Component\Type\CardGameTeam;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\DummyFaceupActionDto;
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
    
    protected function IsDummy(): bool
    {
        $DummyPlayer = PlayerPositionExtensions::GetTeammate( $this->Game->CurrentContract->Player );
        
        return $this->Game->CurrentPlayer == $DummyPlayer;
    }
    
    protected function DummyFaceupAction(): void
    {
        $DummyPlayer = PlayerPositionExtensions::GetTeammate( $this->Game->CurrentContract->Player );
        
        $action = new DummyFaceupActionDto();
        $action->DummyPlayer    = $DummyPlayer;
        $action->Player         = $this->Game->CurrentContract->Player;
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
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
        $bidValue = $action->bid->Value;
        $this->logger->log( "Contract Bridge DoBid: {$bidValue}", 'GameManager' );
        
        if ( $action->bid->Trump == BidTrump::Pass->value() ) {
            //$this->Game->ConsecutivePasses++;
        }
        
        $bid = new Bid( $action->bid->Player, BidTrump::fromValue( $action->bid->Trump ) );
        $bid->Value = $bidValue;
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
}
