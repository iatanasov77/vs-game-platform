<?php namespace App\Component\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use React\Async;
use Ratchet\RFC6455\Messaging\Frame;

use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\GameMechanics\RoundResult;

// Types
use App\Component\Type\CardGameTeam;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\BiddingStartedActionDto;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\PlayingStartedActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\TrickEndedActionDto;
use App\Component\Dto\Actions\RoundEndedActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;

abstract class CardGameManager extends AbstractGameManager
{
    public function StartGame(): void
    {
        $this->Game->ThinkStart = new \DateTime( 'now' );
        
        $gameDto = Mapper::CardGameToDto( $this->Game );
        // $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
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
            $this->logger->log( 'First Bid State !!!', 'FirstBidState' );
            
            $this->Game->SetFirstBidWinner();
            
            $biddingStartedAction = new BiddingStartedActionDto();
            
            $biddingStartedAction->deck = \array_values( $this->Game->Deck->Cards()->map(
                function( $entry ) {
                    return Mapper::CardToDto( $entry );
                }
            )->toArray() );
            
            foreach ( $this->Game->Players as $key => $player ) {
                $biddingStartedAction->playerCards[$key] = $this->Game->playerCards[$key]->map(
                    function( $entry ) use ( $player ) {
                        return Mapper::CardToDto( $entry, $player->PlayerPosition );
                    }
                )->toArray();
            }
            
            $biddingStartedAction->firstToBid = $this->Game->CurrentPlayer;
            
            $validBids = $this->Game->AvailableBids->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
            $biddingStartedAction->validBids = \array_values( $validBids );
            
            $biddingStartedAction->timer = Game::ClientCountDown;
            
            $this->Send( $this->Clients->get( PlayerPosition::South->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::East->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::North->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::West->value ), $biddingStartedAction );
        }
    }
    
    public function EndRound(): void
    {
        $this->logger->log( "Card_Game_Round_Ended !!!", 'GameManager' );
        
        $score = $this->Game->GetNewScore();
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        $this->Game->PlayState = GameState::roundEnded;
        
        $this->Game->southNorthPoints += $score->SouthNorthPoints;
        $this->Game->eastWestPoints += $score->EastWestPoints;
        $this->Game->hangingPoints = $score->HangingPoints;
        
        $action = new RoundEndedActionDto();
        $action->game = Mapper::CardGameToDto( $this->Game );
        
        $newScore = Mapper::RoundResultToDto( $score );
        $newScore->contract = Mapper::BidToDto( $this->Game->CurrentContract );
        $action->newScore = $newScore;
        
        // Debug Tricks
        $action->SouthNorthTricks = $this->Game->SouthNorthTricks->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry );
            }
        )->toArray();
        
        $action->EastWestTricks = $this->Game->EastWestTricks->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry );
            }
        )->toArray();
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
        
        $winner = $this->GetWinner();
        if ( $winner ) {
            $this->logger->log( "{$winner->value} won Game {$this->Game->Id}", 'GameManager' );
            $this->EndGame( $winner );
        }
    }
    
    public function StartNewRound(): void
    {
        $this->Game->roundNumber++;
        $this->Game->PlayState = GameState::firstBid;
        $this->Game->Deck = new Deck();
        
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        $this->Game->SouthNorthTricks = new ArrayCollection();
        $this->Game->EastWestTricks = new ArrayCollection();
        
        $this->StartGame();
    }
    
    abstract protected function DoBid( BidMadeActionDto $action ): void;
    
    abstract protected function PlayCard( PlayCardActionDto $action ): void;
    
    protected function PlayRound( WebsocketClientInterface $socket ): void
    {
        $this->logger->log( "Play Round !!! PlayState: " . $this->Game->PlayState->value . " CurrentPlayer: " . $this->Game->CurrentPlayer->value, 'GameManager' );
        if ( $this->Game->PlayState != GameState::roundEnded && $this->AisTurn() ) {
            $this->logger->log( "NewTurn for AI", 'SwitchPlayer' );
            if ( $this->Game->PlayState == GameState::bidding ) {
                $this->EnginBids( $socket );
            } else {
                $this->EnginPlayCard( $socket );
            }
            
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        }
    }
    
    protected function StartGamePlay(): void
    {
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        
        $playingStartedAction = new PlayingStartedActionDto();
        
        $playingStartedAction->deck = \array_values( $this->Game->Deck->Cards()->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry );
            }
        )->toArray() );
        
        foreach ( $this->Game->Players as $key => $player ) {
            $playingStartedAction->playerCards[$key] = $this->Game->playerCards[$key]->map(
                function( $entry ) use ( $player ) {
                    return Mapper::CardToDto( $entry, $player->PlayerPosition );
                }
            )->toArray();
            
            $playerAnnounces = $this->Game->GetAvailableAnnounces( $this->Game->playerCards[$key] );
            $this->logger->log( "Player Announces" . \print_r( $playerAnnounces->toArray(), true ), 'GameManager' );
            $playingStartedAction->playerAnnounces[$key] = $playerAnnounces->map(
                function( $entry ) use ( $player ) {
                    return Mapper::AnnounceToDto( $entry, $player->PlayerPosition );
                }
            )->toArray();
            
            foreach ( $playerAnnounces as $announce ) {
                $announce->Player = $player->PlayerPosition;
                $this->Game->announces[] = $announce;
            }
        }
        
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$this->Game->CurrentPlayer->value],
            $this->Game->CurrentContract,
            new ArrayCollection()
        );
        
        $playingStartedAction->firstToPlay = $this->Game->firstInRound;
        $playingStartedAction->contract = Mapper::BidToDto( $this->Game->CurrentContract );
        $playingStartedAction->validCards = $this->Game->ValidCards->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, $this->Game->CurrentPlayer );
            }
        )->toArray();
        $playingStartedAction->timer = Game::ClientCountDown;
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $playingStartedAction );
        
        $this->Game->PlayState = GameState::playing;
        
        $this->logger->log( "Start Game Play !!!", 'GameManager' );
        $socket = $this->Clients->get( PlayerPosition::South->value );
        $this->PlayRound( $socket );
    }
    
    protected function SendTrickWinner( PlayerPosition $winner ): void
    {
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$winner->value],
            $this->Game->CurrentContract,
            new ArrayCollection()
        );
        $game = Mapper::CardGameToDto( $this->Game );
        
        $promise = Async\async( function () use ( $game ) {
            Async\delay( 1.2 );
            
            $trickEndedAction = new TrickEndedActionDto();
            $trickEndedAction->game = $game;
            
            $this->Send( $this->Clients->get( PlayerPosition::South->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::East->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::North->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::West->value ), $trickEndedAction );
        })();
        Async\await( $promise );
    }
    
    protected function SaveWinner( CardGameTeam $team ): ?array
    {
        //return [$scoreBlack, $scoreWhite];
        return [null, null];
    }
    
    abstract protected function GetWinner(): ?CardGameTeam;
    
    protected function SendWinner( CardGameTeam $team, ?RoundResult $newScore = null ): void
    {
        $game = Mapper::CardGameToDto( $this->Game );
        $game->winner = $team;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        //$gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $gameEndedAction );
    }
    
    protected function Resign( PlayerPosition $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( CardGameTeam $winner ): void
    {
        //$this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner );
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket ): void
    {
        if ( $socket != null ) {
            $this->logger->log( "Closing client", 'ExitGame' );
            $socket->close( Frame::CLOSE_NORMAL );
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerPosition::South->value ) ) {
                $this->Clients->set( PlayerPosition::South->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::North->value ) ) {
                $this->Clients->set( PlayerPosition::North->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::East->value ) ) {
                $this->Clients->set( PlayerPosition::East->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::West->value ) ) {
                $this->Clients->set( PlayerPosition::West->value, null );
            }
        }
    }
}
