<?php namespace App\Component\Rules\CardGame\ConractBridgeGameMechanics;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\EventListener\Event\CardGameRoundEndedEvent;
use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;

use App\Component\Rules\CardGame\Helper;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Card;
use App\Component\Type\CardSuit;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Rules\CardGame\PlayCardAction;

class RoundManager
{
    use Helper;
    
    /** @var Game */
    private Game $game;
    
    /** @var GameLogger */
    private  $logger;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var ContractManager */
    private ContractManager $contractManager;
    
    /** @var TricksManager */
    private TricksManager $tricksManager;
    
    /** @var ScoreManager */
    private ScoreManager $scoreManager;
    
    public function __construct( Game $game, GameLogger $logger, EventDispatcherInterface $eventDispatcher )
    {
        $this->game             = $game;
        $this->logger           = $logger;
        $this->eventDispatcher  = $eventDispatcher;
        
        /*
        $this->contractManager = new ContractManager( $this->game, $this->logger );
        $this->tricksManager = new TricksManager( $this->game, $this->logger );
        $this->scoreManager = new ScoreManager( $this->game, $this->logger );
        */
        
        $this->game->Deck = new Deck( $this->game->GameCode );
        $this->game->playerCards = new ArrayCollection();
        foreach ( $this->game->Players as $key => $player ) {
            $this->game->playerCards->set( $key, new ArrayCollection() );
        }
    }
    
    public function PlayRound(): ?PlayerPosition
    {
        if ( $this->game->PlayState == GameState::firstBid ) {
            // Initialize the cards
            $this->game->Deck->Shuffle();
            $this->game->playerCards[PlayerPosition::South->value]->clear();
            $this->game->playerCards[PlayerPosition::East->value]->clear();
            $this->game->playerCards[PlayerPosition::North->value]->clear();
            $this->game->playerCards[PlayerPosition::West->value]->clear();
            
            // Deal 5 cards to each player
            $this->DealCards( 13 );
            
            //$this->contractManager->StartNewRound();
        }
        
        if ( $this->game->PlayState == GameState::bidding ) {
            if ( $this->game->ConsecutivePasses == 4 ) {
                $this->logger->log( 'Consecutive Passes Exceeded !!!', 'RoundManager' );
                
                $this->game->PlayState = GameState::roundEnded;
                $this->eventDispatcher->dispatch( new CardGameRoundEndedEvent( $this->game ), CardGameRoundEndedEvent::NAME );
            }
            
            //$this->logger->log( 'CurrentContract: ' . \print_r( $this->game->CurrentContract, true ), 'RoundManager' );
            if ( $this->game->CurrentContract->ReKontraPlayer ) {
                $lastBidPlayer = $this->game->CurrentContract->ReKontraPlayer;
            } elseif ( $this->game->CurrentContract->KontraPlayer ) {
                $lastBidPlayer = $this->game->CurrentContract->KontraPlayer;
            } else {
                $lastBidPlayer = $this->game->CurrentContract->Player;
            }
            
            if ( $this->game->CurrentPlayer == $lastBidPlayer && $this->game->ConsecutivePasses == 3 ) {
                $this->logger->log( 'Consecutive Passes Exceeded !!!', 'RoundManager' );
                
                $this->game->PlayState = GameState::firstRound;
            }
        }
        
        if ( $this->game->PlayState == GameState::playing ) {
            if ( $this->tricksManager->GetTrickActionNumber() == 4 ) {
                $this->game->trickNumber++;
                return $this->tricksManager->GetTricksWinner();
            }
        }
        
        return null;
    }
    
    public function SetContract( Bid $bid, PlayerPosition $nextPlayer ): void
    {
        $this->contractManager->SetContract( $bid, $nextPlayer );
    }
    
    public function GetValidCards( Collection $playerCards, Bid $currentContract, Collection $trickActions ): Collection
    {
        return $this->tricksManager->GetValidCards( $playerCards, $currentContract, $trickActions );
    }
    
    public function GetAvailableAnnounces( Collection $playerCards ): Collection
    {
        return $this->tricksManager->GetAvailableAnnounces( $playerCards );
    }
    
    public function IsBeloteAllowed( Collection $playerCards, EnumBitMask $contract, Collection $currentTrickActions, Card $playedCard ): bool
    {
        return $this->tricksManager->IsBeloteAllowed( $playerCards, $contract, $currentTrickActions, $playedCard );
    }
    
    public function GetTrickActionNumber(): int
    {
        return $this->tricksManager->GetTrickActionNumber();
    }
    
    public function GetTrickActions(): Collection
    {
        return $this->tricksManager->GetTrickActions();
    }
    
    public function AddTrickAction( PlayCardAction $action ): void
    {
        $this->tricksManager->AddTrickAction( $action );
    }
    
    public function GetScore(
        Bid $contract,
        Collection $southNorthTricks,
        Collection $eastWestTricks,
        Collection $announces,
        int $hangingPoints,
        ?PlayerPosition $lastTrickWinner
    ): RoundResult {
        return $this->scoreManager->GetScore(
            $contract,
            $southNorthTricks,
            $eastWestTricks,
            $announces,
            $hangingPoints,
            $lastTrickWinner
        );
    }
    
    private function DealCards( int $count ): void
    {
        $dealToPlayer   = $this->game->firstInRound;
        for ( $i = 0; $i < $count; $i++ )
        {
            while( true ) {
                $card = $this->game->Deck->GetNextCard();
                $this->game->Deck->RemoveCard( $card );
                
                $this->game->playerCards[$dealToPlayer->value][] = $card;
                $dealToPlayer = PlayerPositionExtensions::Next( $dealToPlayer );
                if( $dealToPlayer === $this->game->firstInRound ) {
                    break;
                }
            }
        }
        
        foreach ( $this->game->playerCards as $k => $cards ) {
            $this->game->playerCards[$k] = $this->sortCardsBySuite( $cards );
        }
    }
    
    private function sortCardsBySuite( Collection $cards ): Collection
    {
        // Group by suit
        $cardsBySuit = [
            CardSuit::Club->value       => new ArrayCollection(),
            CardSuit::Diamond->value    => new ArrayCollection(),
            CardSuit::Heart->value      => new ArrayCollection(),
            CardSuit::Spade->value      => new ArrayCollection(),
        ];
        foreach ( $cards as $card ) {
            $cardsBySuit[$card->Suit->value][] = $card;
        }
        
        // Check each suit
        for ( $suitIndex = 0; $suitIndex < 4; $suitIndex++ ) {
            $cardsBySuit[$suitIndex] = $this->sortCards( $cardsBySuit[$suitIndex] );
        }
        
        return new ArrayCollection( \array_merge(
            $cardsBySuit[CardSuit::Club->value]->toArray(),
            $cardsBySuit[CardSuit::Diamond->value]->toArray(),
            $cardsBySuit[CardSuit::Heart->value]->toArray(),
            $cardsBySuit[CardSuit::Spade->value]->toArray()
        ));
    }
}
