<?php namespace App\Component\Manager;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
            
            $biddingStartedAction->validBids = $this->Game->AvailableBids->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
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
        
        $action = new RoundEndedActionDto();
        $action->game = Mapper::CardGameToDto( $this->Game );
        $action->newScore = Mapper::RoundResultToDto( $score );
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
    }
    
    public function StartNewRound(): void
    {
        $this->Game->roundNumber++;
        $this->Game->PlayState = GameState::firstBid;
        $this->Game->Deck = new Deck();
        
        $this->StartGame();
    }
    
    abstract protected function DoBid( BidMadeActionDto $action ): void;
    
    abstract protected function PlayCard( PlayCardActionDto $action ): void;
    
    protected function StartGamePlay(): void
    {
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
        }
        
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$this->Game->CurrentPlayer->value],
            $this->Game->CurrentContract,
            new ArrayCollection()
        );
        
        $playingStartedAction->firstToPlay = $this->Game->CurrentPlayer;
        $playingStartedAction->contract = Mapper::BidToDto( $this->Game->CurrentContract );
        $playingStartedAction->validCards = $this->Game->ValidCards->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, PlayerPosition::South );
            }
        )->toArray();
        $playingStartedAction->timer = Game::ClientCountDown;
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $playingStartedAction );
        
        $this->Game->PlayState = GameState::playing;
    }
    
    protected function SendTrickWinner( PlayerPosition $winner, RoundResult $newScore ): void
    {
        $game = Mapper::CardGameToDto( $this->Game );
        
        $trickEndedAction = new TrickEndedActionDto();
        $trickEndedAction->game = $game;
        $trickEndedAction->newScore = Mapper::RoundResultToDto( $newScore );
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $trickEndedAction );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $trickEndedAction );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $trickEndedAction );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $trickEndedAction );
    }
    
    protected function SaveWinner( CardGameTeam $team ): ?array
    {
        
    }
    
    protected function GetWinner(): ?CardGameTeam
    {
        $winner = null;
        
        
        return $winner;
    }
    
    protected function SendWinner( CardGameTeam $team, ?array $newScore ): void
    {
        
    }
    
    protected function Resign( PlayerPosition $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( PlayerPosition $winner ): void
    {
        //$this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        //$newScore = $this->SaveWinner( $winner );
        //$this->SendWinner( $winner, $newScore );
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
