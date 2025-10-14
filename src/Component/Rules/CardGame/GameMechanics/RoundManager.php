<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

class RoundManager
{
    /** @var Game */
    private Game $game;
    
    /** @var GameLogger */
    private  $logger;
    
    /** @var ContractManager */
    private ContractManager $contractManager;
    
    /** @var TricksManager */
    private TricksManager $tricksManager;
    
    /** @var ScoreManager */
    private ScoreManager $scoreManager;
    
    public function __construct( Game $game, GameLogger $logger )
    {
        $this->game = $game;
        $this->logger = $logger;
        
        $this->contractManager = new ContractManager( $this->game, $this->logger );
        $this->tricksManager = new TricksManager( $this->game, $this->logger );
//         $this->scoreManager = new ScoreManager();

        $this->game->Deck = new Deck();
        $this->game->playerCards = new ArrayCollection();
        foreach ( $this->game->Players as $key => $player ) {
            $this->game->playerCards->set( $key, new ArrayCollection() );
        }
    }
    
    public function PlayRound(): void
    {
        if ( $this->game->PlayState == GameState::firstBid ) {
            // Initialize the cards
            $this->game->Deck->Shuffle();
            $this->game->playerCards[PlayerPosition::South->value]->clear();
            $this->game->playerCards[PlayerPosition::East->value]->clear();
            $this->game->playerCards[PlayerPosition::North->value]->clear();
            $this->game->playerCards[PlayerPosition::West->value]->clear();
            
            // Deal 5 cards to each player
            $this->DealCards( 5 );
            
            $this->contractManager->StartNewRound();
        }
        
        if ( $this->game->PlayState == GameState::bidding ) {
            if ( ! $this->game->CurrentContract && $this->game->ConsecutivePasses == 4 ) {
                $this->logger->log( 'Consecutive Passes Exceeded !!!', 'RoundManager' );
                
                $this->game->PlayState = GameState::ended;
            }
            
            if ( $this->game->CurrentPlayer == $this->game->CurrentContract->Player && $this->game->ConsecutivePasses == 3 ) {
                $this->logger->log( 'Consecutive Passes Exceeded !!!', 'RoundManager' );
                $this->logger->log( 'CurrentContract: ' . \print_r( $this->game->CurrentContract, true ), 'RoundManager' );
                
                $this->game->PlayState = GameState::firstRound;
                $this->DealCards( 3 );
            }
        }
    }
    
    public function SetContract( Bid $bid ): void
    {
        $this->contractManager->SetContract( $bid );
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
    }
}
