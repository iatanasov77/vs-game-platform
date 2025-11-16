<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;

/**
 * ChessEngine in C#: https://www.syedgakbar.com/projects/chess
 */
class ChessGame extends Game
{
    public function SetStartPosition(): void
    {
        /*
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
        */
    }
    
    public function SetFirstMoveWinner(): void
    {
        if ( $this->PlayState == GameState::firstMove ) {
            $this->CurrentPlayer = PlayerColor::Black;
            $this->PlayState = GameState::playing;
        }
    }
    
    public function FakeMove( int $v1, int $v2 ): void
    {
        $this->SetFirstMWinner();
    }
    
    public function StartGame(): void
    {
        $this->SetFirstMoveWinner();
        
        // $this->logger->log( 'CurrentPlayer: ' . $this->CurrentPlayer->value, 'FirstThrowState' );
        /*
        $this->ClearMoves( $this->ValidMoves );
        $this->_GenerateMoves( $this->ValidMoves );
        */
        
        Game::$DebugValidMoves++;
        //$this->logger->debug( $this->ValidMoves, 'ValidMoves_' . Game::$DebugValidMoves .  '.txt' );
    }
    
    public function GenerateMoves(): array
    {
        $moves = new ArrayCollection();
        $this->_GenerateMoves( $moves );
    }
    
    protected function _GenerateMoves( Collection &$moves ): void
    {
        $currentPlayer  = $this->CurrentPlayer;
    }
}
