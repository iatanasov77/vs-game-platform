<?php namespace App\Component\Manager\GameMechanics;

trait RoundManager
{
    public function PlayRound()
    {
        // Initialize the cards
        $this->Game->deck->Shuffle();
        $this->Game->playerCards[0]->clear();
        $this->Game->playerCards[1]->clear();
        $this->Game->playerCards[2]->clear();
        $this->Game->playerCards[3]->clear();
        
        // Deal 5 cards to each player
        $this->DealCards( 5 );
    }
    
    protected function DealCards( int $count ): void
    {
        for ( $i = 0; $i < $count; $i++ )
        {
            $this->Game->playerCards[0][] = $this->Game->deck->GetNextCard();
            $this->Game->playerCards[1][] = $this->Game->deck->GetNextCard();
            $this->Game->playerCards[2][] = $this->Game->deck->GetNextCard();
            $this->Game->playerCards[3][] = $this->Game->deck->GetNextCard();
        }
    }
}
