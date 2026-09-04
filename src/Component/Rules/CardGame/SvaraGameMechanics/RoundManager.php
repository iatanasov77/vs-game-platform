<?php namespace App\Component\Rules\CardGame\SvaraGameMechanics;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

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
use App\Component\Type\BidTrump;

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
        
        $this->contractManager = new ContractManager( $this->game, $this->logger );
        /*
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
        $this->logger->log( "Svara GameState on PlayRound: {$this->game->PlayState->value}", 'RoundManager' );
        
        if ( $this->game->PlayState == GameState::firstBid ) {
            // Initialize the cards
            $this->game->Deck->Shuffle();
            $this->game->playerCards[PlayerPosition::South->value]->clear();
            $this->game->playerCards[PlayerPosition::East->value]->clear();
            $this->game->playerCards[PlayerPosition::North->value]->clear();
            $this->game->playerCards[PlayerPosition::West->value]->clear();
            
            // Deal 5 cards to each player
            $this->DealCards( 3 );
            $this->contractManager->StartNewRound();
        }
        
        if ( $this->game->PlayState == GameState::bidding ) {
            
        }
        
        if ( $this->game->PlayState == GameState::playing ) {
            $this->logger->log( "Trick Number: {$this->game->trickNumber}", 'RoundManager' );
            
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
        $this->logger->log( "Add Trick Action: {$this->tricksManager->GetTrickActionNumber()}", 'RoundManager' );
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
