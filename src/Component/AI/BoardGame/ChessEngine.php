<?php namespace App\Component\AI\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessPieceType;
use App\Component\Rules\BoardGame\Game;
use App\Component\Rules\BoardGame\ChessSide;
use App\Component\Rules\BoardGame\ChessSquare;
use App\Component\Rules\BoardGame\ChessMove;
use App\Component\Manager\AbstractGameManager;

class ChessEngine extends Engine
{
    /** @var int */
    const MIN_SCORE = -1000000;		// Minimum limit of negative for integer
    
    /** @var int */
    const MAX_SCORE = 1000000;		// Maximum limit of positive integer
    
    /** @var bool */
    private $GameNearEnd;			// True when the game is near the end
    
    /** @var int */
    private $TotalMovesAnalyzed;	// Total no. of moves analzyed by the player
    
    protected function _GenerateMovesSequence( Collection &$sequences, Collection &$moves, int $diceIndex, Game $game ): void
    {
        
    }
    
    // Get the best move available to the player
    public function GetFixBestMove(): ChessMove
    {
        TimeSpan ElapsedTime= new TimeSpan(1);		// Total elpased time
        $BestMove = null;		// The best move for the current position
        
        $currentSide = new ChessSide( $this->EngineGame->CurrentPlayer );
        $TotalMoves = $this->EngineGame->Rules->GenerateAllLegalMoves( $currentSide ); // Get all the legal moves for the current side
        $PlayerCells = $this->EngineGame->GetSideCell( $currentSide->type );
        
        $alpha = self::MIN_SCORE;	// The famous Alpha & Beta are set to their initial values
        $beta  = self::MAX_SCORE;	// at the start of each increasing search depth iteration
        
        $depth = 3;
        
        // Loop through all the legal moves and get the one with best score
        foreach ( $TotalMoves as $move ) {
            // Now to get the effect of this move; execute this move and analyze the board
            $this->EngineGame->Rules->ExecuteMove( $move );
            
            $otherPlayerSide = $currentSide = new ChessSide( $this->EngineGame->OtherPlayer( $this->EngineGame->CurrentPlayer ) );
            $move->Score = - $this->AlphaBeta( $otherPlayerSide, $depth - 1, -$beta, -$alpha );
            $this->EngineGame->Rules->UndoMove( $move );	// undo the move
            
            // If the score of the move we just tried is better than the score of the best move we had
            // so far, at this depth, then make this the best move.
            if ( $move.Score > $alpha ) {
                $BestMove = $move;
                $alpha = $move->Score;
            }
        }
        
        return $BestMove;
    }
    
    // Get the best move available to the player
    public function GetBestMove(): ChessMove
    {
        // TimeSpan ElapsedTime = new TimeSpan(1);		// Total elpased time
        $BestMove = null;		// The best move for the current position
        
        $currentSide = new ChessSide( $this->EngineGame->CurrentPlayer );
        $TotalMoves = $this->EngineGame->Rules->GenerateAllLegalMoves( $currentSide ); // Get all the legal moves for the current side
        
        // Now we use the Iterative deepening technique to search the best move
        // The idea is just simple, we will keep searching in the more and more depth
        // as long as we don't time out.
        // So, it means that when we have less moves, we can search more deeply and which means
        // better chess game.
        
        //DateTime ThinkStartTime = DateTime.Now;
        //int MoveCounter;
        //Random RandGenerator= new Random();
        
        // Game is near the end, or the current player is under check
        if ( $this->EngineGame->GetSideCell( $this->EngineGame->CurrentPlayer )->count() <= 5 || $TotalMoves->count() <= 5 ) {
            $this->GameNearEnd = true;
        }
            
        // Game is near the end, or the Enemy player is under check
        if ( $currentSide->isBlack() ) {
            $EnemySide = new ChessSide( PlayerColor::White );
        } else {
            $EnemySide = new ChessSide( PlayerColor::Black );
        }
        
        if ( $this->EngineGame->GetSideCell( $EnemySide )->count() <= 5 || $this->EngineGame->Rules->GenerateAllLegalMoves( $EnemySide )->count() <= 5 ) {
            $this->GameNearEnd = true;
        }
            
        $this->TotalMovesAnalyzed = 0;		// Reset the total moves anazlye counter
        
        //for ( depth = 1;; depth++ ) {	// Keep doing a depth search
            $alpha = self::MIN_SCORE;	// The famous Alpha & Beta are set to their initial values
            $beta  = self::MAX_SCORE;	// at the start of each increasing search depth iteration
            $MoveCounter = 0;	// Initialize the move counter variable
            
            // Loop through all the legal moves and get the one with best score
            foreach ( $TotalMoves as $move ) {
                $MoveCounter++;
                
                // Now to get the effect of this move; execute this move and analyze the board
                $this->EngineGame->Rules->ExecuteMove( $move );
                
                $move->Score = - $this->AlphaBeta( $EnemySide, $depth - 1, -$beta, -$alpha );
                $this->TotalMovesAnalyzed++;	// Increment move counter
                
                $this->EngineGame->Rules->UndoMove( $move );	// undo the move
                
                // If the score of the move we just tried is better than the score of the best move we had
                // so far, at this depth, then make this the best move.
                if ( $move->Score > $alpha ) {
                    $BestMove = $move;
                    $alpha = $move->Score;
                }
                
                //m_Rules.ChessGame.NotifyComputerThinking(depth, MoveCounter, TotalMoves.Count, $this->TotalMovesAnalyzed, BestMove );
                /*
                // Check if the user time has expired
                ElapsedTime=DateTime.Now - ThinkStartTime;
                if ( ElapsedTime.Ticks > (m_MaxThinkTime.Ticks) ) {	// More than 75 percent time is available
                    break;							// Force break the loop
                }
                */
            }
            /*
            // Check if the user time has expired
            ElapsedTime=DateTime.Now - ThinkStartTime;
            if ( ElapsedTime.Ticks > (m_MaxThinkTime.Ticks*0.25))	// More than 75 percent time is available
                break;							// Force break the loop
            */
        //}
        
        //m_Rules.ChessGame.NotifyComputerThinking(depth, MoveCounter, TotalMoves.Count, $this->TotalMovesAnalyzed, BestMove );
        return $BestMove;
    }
    
    // Alpha and beta search to recursively travers the tree to calculate the best move
    private function AlphaBeta( ChessSide $PlayerSide, int $depth, int $alpha, int $beta ): int
    {
        // Before we do anything, let's try the null move. It's like giving the opponent
        // a free shot and see if he can damage us. If he can't, we are in a better position and
        // can nock down him
        
        // "Adaptive" Null-move forward pruning
        $R = ( $depth > 6 ) ? 3 : 2; //  << This is the "adaptive" bit
        // The rest is normal Null-move forward pruning
        if ( $depth >= 2 && ! $this->GameNearEnd ) {	// disable null move for now
            $EnemySide = new ChessSide( $PlayerSide->Enemy() );
            $val = -$this->AlphaBeta( $EnemySide, $depth  - $R - 1, -$beta, -$beta + 1 ); // Try a Null Move
            if ( $val >= $beta ) { // All the moves can be skipped, i.e. cut-off is possible
                return $beta;
            }
        }
        
        // This variable is set to true when we have found at least one Principle variation node.
        // Principal variation (PV) node is the one where One or more of the moves will return a score greater than alpha (a PV move), but none will return a score greater than or equal to beta.
        $bFoundPv = false;
        
        // Check if we have reached at the end of the search
        if ( $depth <= 0 ) {
            // Check if need to do queiscent search to avoid horizontal effect
            if ( $this->EngineGame->DoQuiescentSearch ) {
                return $this->QuiescentSearch( $PlayerSide, $alpha, $beta );
            } else {
                return $this->EngineGame->Rules->Evaluate( $PlayerSide );	// evaluate the current board position
            }
        }
        // Get all the legal moves for the current side
        $TotalMoves = $this->EngineGame->Rules->GenerateAllLegalMoves( $PlayerSide );
        
        // Loop through all the legal moves and get the one with best score
        foreach ( $TotalMoves as $move ) {
            // Now to get the effect of this move; execute this move and analyze the board
            $this->EngineGame->Rules->ExecuteMove( $move );
            
            // Principle variation node is found
            if ( $bFoundPv && $this->EngineGame->DoPrincipleVariation ) {
                $EnemySide = new ChessSide( $PlayerSide->Enemy() );
                $val = -$this->AlphaBeta( $EnemySide, $depth - 1, -$alpha - 1, -$alpha );
                if ( ( $val > $alpha ) && ( $val < $beta ) ) { // Check for failure.
                    $val=-$this->AlphaBeta( $EnemySide, $depth - 1, -$beta, -$alpha ); // Do normal Alpha beta pruning
                }
            } else {
                $EnemySide = new ChessSide( $PlayerSide->Enemy() );
                $val = -$this->AlphaBeta( $EnemySide, $depth - 1, -$beta, -$alpha ); // Do normal Alpha beta pruning
            }
            
            $this->TotalMovesAnalyzed++;	// Increment move counter
            $this->EngineGame->Rules->UndoMove( $move );	// undo the move
            
            // This move will never played by the opponent, as he has already better options
            if ( $val >= $beta ) {
                return $beta;
            }
            
            // This is the best move for the current side (found so far)
            if ( $val > $alpha ) {
                $alpha = $val;
                $bFoundPv = true;		// we have found a principle variation node
            }
        }
        
        return $alpha;
    }
    
    // Do the queiscent search to avoid horizontal effect
    private function QuiescentSearch( ChessSide $PlayerSide, int $alpha, int $beta ): int
    {
        $val = $this->EngineGame->Rules->Evaluate( $PlayerSide );
        
        if ( $val >= $beta ) { // We have reached beta cutt off
            return $beta;
        }
            
        if ( $val > $alpha ) { // found alpha cut-off
            $alpha = $val;
        }
            
        // Get all the legal moves for the current side
        $TotalMoves = $this->EngineGame->Rules->GenerateGoodCaptureMoves( $PlayerSide );
        
        // Loop through all the legal moves and get the one with best score
        foreach ( $TotalMoves as $move ) {
            // Now to get the effect of this move; execute this move and analyze the board
            $this->EngineGame->Rules->ExecuteMove( $move );
            
            $EnemySide = new ChessSide( $PlayerSide->Enemy() );
            $val = -$this->QuiescentSearch( $EnemySide, -$beta, -$alpha );
            
            $this->EngineGame->Rules->UndoMove( $move );	// undo the move
            
            if ( $val >= $beta ) { // We have reached beta cutt off
                return $beta;
            }
            
            if ( $val > $alpha ) { // found alpha cut-off
                $alpha = $val;
            }
        }
        
        return $alpha;
    }
}
