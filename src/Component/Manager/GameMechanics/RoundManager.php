<?php namespace App\Component\Manager\GameMechanics;

use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

trait RoundManager
{
    use PlayerPositionExtensions;
    
    public function PlayRound()
    {
        if ( $this->Game->PlayState = GameState::firstBid ) {
            // Initialize the cards
            $this->Game->deck->Shuffle();
            $this->Game->Players[PlayerPosition::South->value]->Cards->clear();
            $this->Game->Players[PlayerPosition::East->value]->Cards->clear();
            $this->Game->Players[PlayerPosition::North->value]->Cards->clear();
            $this->Game->Players[PlayerPosition::West->value]->Cards->clear();
        }
        $this->Game->SetFirstAnnounceWinner();
        
        if ( $this->PlayState = GameState::bidding ) {
            // Deal 5 cards to each player
            $this->DealCards( 5 );
        } else {
            $this->DealCards( 3 );
        }
    }
    
    protected function DealCards( int $count ): void
    {
        $dealToPlayer   = $this->Game->CurrentPlayer;
        for ( $i = 0; $i < $count; $i++ )
        {
            while( true ) {
                $this->Game->Players[$dealToPlayer->value]->Cards[] = $this->Game->deck->GetNextCard();
                $dealToPlayer = self::Next( $dealToPlayer );
                if( $dealToPlayer === $this->Game->CurrentPlayer ) {
                    break;
                }
            }
        }
    }
}
