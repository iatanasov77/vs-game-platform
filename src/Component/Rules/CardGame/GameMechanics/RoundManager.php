<?php namespace App\Component\Rules\CardGame\GameMechanics;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Type\PlayerPosition;

class RoundManager
{
    use PlayerPositionExtensions;
    
    private Game $game;
    
    private ContractManager $contractManager;
    
    private TricksManager $tricksManager;
    
    private ScoreManager $scoreManager;
    
    private Deck $deck;
    
    private Collection $playerCards;
    
    public function __construct( Game $game )
    {
        $this->game = $game;
        $this->contractManager = new ContractManager( $this->game );
//         $this->tricksManager = new TricksManager( southPlayer, eastPlayer, northPlayer, westPlayer );
//         $this->scoreManager = new ScoreManager();
        $this->deck = new Deck();
        
        $this->playerCards = new ArrayCollection();
        foreach ( $this->game->Players as $key => $player ) {
            $this->playerCards->set( $key, new ArrayCollection() );
        }
    }
    
    public function PlayRoundBiddingPhase(): Collection
    {
        // Initialize the cards
        $this->deck->Shuffle();
        $this->playerCards[PlayerPosition::South->value]->clear();
        $this->playerCards[PlayerPosition::East->value]->clear();
        $this->playerCards[PlayerPosition::North->value]->clear();
        $this->playerCards[PlayerPosition::West->value]->clear();
        
        $this->game->SetFirstBidWinner();
        
        // Deal 5 cards to each player
        $this->DealCards( 5 );
        
        return $this->playerCards;
    }
    
    public function GetContract( Collection &$bids ): Bid
    {
        return $this->contractManager->GetContract(
            $this->game->roundNumber,
            $this->game->firstInRound,
            $this->game->southNorthPoints,
            $this->game->eastWestPoints,
            $this->playerCards,
            $bids
        );
    }
    
    public function PlayRoundPlayingPhase(): Collection
    {
        // Deal 3 more cards to each player
        $this->DealCards( 3 );
        
        return $this->playerCards;
    }
    
    private function DealCards( int $count ): void
    {
        $dealToPlayer   = $this->game->CurrentPlayer;
        for ( $i = 0; $i < $count; $i++ )
        {
            while( true ) {
                $this->playerCards[$dealToPlayer->value][] = $this->deck->GetNextCard();
                $dealToPlayer = self::Next( $dealToPlayer );
                if( $dealToPlayer === $this->game->CurrentPlayer ) {
                    break;
                }
            }
        }
    }
}
