<?php namespace App\Component\Rules\CardGame\GameMechanics;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

class RoundManager
{
    
    private Game $game;
    
    private ContractManager $contractManager;
    
    private TricksManager $tricksManager;
    
    private ScoreManager $scoreManager;
    
    public function __construct( Game $game )
    {
        $this->game = $game;
        
        
        $this->contractManager = new ContractManager( $this->game );
//         $this->tricksManager = new TricksManager( southPlayer, eastPlayer, northPlayer, westPlayer );
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
        
        // Deal 3 more cards to each player
        //$this->DealCards( 3 );
    }
    
    public function SetContract( Bid $bid ): void
    {
        $this->contractManager->SetContract( $bid );
    }
    
    private function DealCards( int $count ): void
    {
        $dealToPlayer   = $this->game->CurrentPlayer;
        for ( $i = 0; $i < $count; $i++ )
        {
            while( true ) {
                $this->game->playerCards[$dealToPlayer->value][] = $this->game->Deck->GetNextCard();
                $dealToPlayer = PlayerPositionExtensions::Next( $dealToPlayer );
                if( $dealToPlayer === $this->game->CurrentPlayer ) {
                    break;
                }
            }
        }
    }
}
