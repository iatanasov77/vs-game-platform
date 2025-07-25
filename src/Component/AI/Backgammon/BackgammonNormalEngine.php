<?php namespace App\Component\AI\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;
use App\Component\Rules\Backgammon\Game;
use App\Component\Rules\Backgammon\Move;
use App\Component\Manager\AbstractGameManager;

class BackgammonNormalEngine extends Engine
{
    protected function _GenerateMovesSequence( Collection &$sequences, Collection &$moves, int $diceIndex, Game $game ): void
    {
        $current = $game->CurrentPlayer;
        $bar = $game->Bars[$current->value];
        $barHasCheckers = $bar->Checkers->filter(
            function( $entry ) use ( $current ) {
                return $entry && $entry->Color === $current;
            }
        )->count();
        $dice = $game->Roll[$diceIndex];
        
        $points = $barHasCheckers ? [ $bar ] : $this->getPointsForPlayer( $current, $game )->toArray();
        
        // There seems to be a big advantage to evaluate points from lowest number
        // If not reversing here, black will win 60 to 40 with same config
        if ( $game->CurrentPlayer == PlayerColor::White ) {
            $points = \array_reverse( $points );
        }
        
        foreach ( $points as $fromPoint ) {
            $fromPointNo = $fromPoint->GetNumber( $game->CurrentPlayer );
            if ( $fromPointNo == 25 ) {
                continue;
            }
            
            $toPoint = $game->Points->filter(
                function( $entry ) use ( $game, $dice, $fromPointNo ) {
                    return $entry->GetNumber( $game->CurrentPlayer ) == $dice->Value + $fromPointNo;
                }
            )->first();
            if (
                $toPoint != null &&
                $toPoint->IsOpen( $game->CurrentPlayer ) &&
                ! $toPoint->IsHome( $game->CurrentPlayer )
            ) {
                // no creation of bearing off moves here. See next block.
                $move = new Move();
                $move->Color = $game->CurrentPlayer;
                $move->From = $fromPoint;
                $move->To = $toPoint;
                
                //copy and make a new list for first dice
                if ( ! isset( $moves[$diceIndex] ) || $moves[$diceIndex]->isNull() ) {
                    $moves[$diceIndex] = $move;
                } else { // a move is already generated for this dice in this sequence. branch off a new.
                    $newMoves = new ArrayCollection();
                    for ( $i = 0; $i < $diceIndex; $i++ ) {
                        $newMoves[] = $moves[$i];
                    }
                    $newMoves[$diceIndex] = $move;
                    
                    // For last checker identical sequences are omitted
                    if (
                        $diceIndex < $game->Roll->count() - 1 ||
                        ! $this->ContainsEntryWithAll( $sequences, $newMoves )
                    ) {
                        $moves = $newMoves;
                        $sequences[]    = $moves;
                    }
                }
                
                if ( $diceIndex < $game->Roll->count() - 1 ) {
                    // Do the created move and recurse to next dice
                    $hit = $game->MakeMove( $move );
                    $this->_GenerateMovesSequence( $sequences, $moves, $diceIndex + 1, $game );
                    $game->UndoMove( $move, $hit );
                }
            } else if ( $game->IsBearingOff( $game->CurrentPlayer ) ) {
                $this->logger->log( "IsBearingOff !!!", 'EngineGenerateMoves' );
                
                // The furthest away checker can be moved beyond home
                $minPoint = $this->calcMinPoint( $game->Points, $game->CurrentPlayer );
                $toPointNo = $fromPointNo == $minPoint ? \min( 25, $fromPointNo + $dice->Value ) : $fromPointNo + $dice->Value;
                $toPoint = $game->Points->filter(
                    function( $entry ) use ( $game, $toPointNo ) {
                        return $entry->GetNumber( $game->CurrentPlayer ) == $toPointNo;
                    }
                )->first();
                
                if ( $toPoint != null && $toPoint->IsOpen( $game->CurrentPlayer ) ) {
                    $move = new Move();
                    $move->Color = $game->CurrentPlayer;
                    $move->From = $fromPoint;
                    $move->To = $toPoint;
                    
                    if ( ! isset( $moves[$diceIndex] ) || $moves[$diceIndex]->isNull() ) {
                        $moves[$diceIndex] = $move;
                    } else {
                        $newMoves = $moves;
                        $newMoves[$diceIndex] = $move;
                        // For last checker identical sequences are omitted
                        if ( $diceIndex < $game->Roll->count() - 1 || ! $this->ContainsEntryWithAll( $sequences, $newMoves ) ) {
                            $moves = $newMoves;
                            $sequences[]    = $moves;
                        }
                    }
                    
                    if ( $diceIndex < $game->Roll->count() - 1 ) {
                        $hit = $game->MakeMove( $move );
                        $this->_GenerateMovesSequence( $sequences, $moves, $diceIndex + 1, $game );
                        $game->UndoMove( $move, $hit );
                    }
                }
            }
        }
    }
}
