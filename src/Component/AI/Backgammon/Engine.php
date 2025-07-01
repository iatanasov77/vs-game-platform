<?php namespace App\Component\AI\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\GameLogger;
use App\Component\Type\PlayerColor;
use App\Component\Rules\Backgammon\Game;
use App\Component\Rules\Backgammon\Move;
use App\Component\Rules\Backgammon\Helper as GameHelper;

class Engine
{
    use GameHelper;
    
    /** @var GameLogger */
    protected $logger;
    
    /** @var Game */
    protected $EngineGame;

    /** @var Config */
    protected $Configuration;
    
    /** @var (int dice1, int dice2)[] */
    protected $_allRolls = null;
    
    public function __construct( GameLogger $logger, Game $game )
    {
        $this->logger           = $logger;
        $this->EngineGame       = $game;
        $this->Configuration    = Config::Trained();
    }

    public function GetBestMoves(): Collection
    {
        $bestMoveSequence   = null;
        $bestScore          = - PHP_FLOAT_MAX;
        $allSequences       = $this->GenerateMovesSequence( $this->EngineGame );
        $this->logger->log( 'Engin GetBestMoves: ' . print_r( $allSequences, true ), 'EngineGenerateMoves' );
        
        $oponent    = $this->EngineGame->OtherPlayer();
        $myColor    = $this->EngineGame->CurrentPlayer;
        
        foreach ( $allSequences as $sequence ) {
            $localSequence = $this->ToLocalSequence( $sequence, $this->EngineGame );
            
            $hits = $this->DoSequence( $localSequence, $this->EngineGame );
            $score = $this->EvaluatePoints( $myColor, $this->EngineGame ) + $this->EvaluateCheckers( $myColor, $this->EngineGame );
            
            $this->UndoSequence( $localSequence, $hits, $this->EngineGame );
            if ( $score > $bestScore ) {
                $bestScore = $score;
                $bestMoveSequence = new ArrayCollection( $sequence );
            }
        }
        
        if ( $bestMoveSequence == null ) {
            return new ArrayCollection();
        }
        
        if ( $myColor == PlayerColor::Black ) {
            $bestMoveSequence   = $bestMoveSequence->filter(
                function( $entry ) {
                    return $entry != null;
                }
            );
            
            $bestMoveSequenceIterator  = $bestMoveSequence->getIterator();
            $bestMoveSequenceIterator->uasort( function ( $a, $b ) {
                return $b->From->BlackNumber <=> $a->From->BlackNumber;
            });
            
            return \iterator_to_array( $bestMoveSequence );
        }

        $bestMoveSequence   = $bestMoveSequence->filter(
            function( $entry ) {
                return $entry != null;
            }
        );
        
        $bestMoveSequenceIterator  = $bestMoveSequence->getIterator();
        $bestMoveSequenceIterator->uasort( function ( $a, $b ) {
            return $b->From->WhiteNumber <=> $a->From->WhiteNumber;
        });
            
        return new ArrayCollection( \iterator_to_array( $bestMoveSequence ) );
    }
    
    public function GenerateMovesSequence( Game $game ): Collection // List<Move[]>
    {
        $sequences  = new ArrayCollection();
        $moves      = [];
        foreach ( $game->Roll as $roll ) {
            $moves[]    = new Move();
        }
        $this->_GenerateMovesSequence( $sequences, $moves, 0, $game );
        
        // Special case. Sometimes the first dice is blocked, but can be moved after next dice
        if ( $sequences->count() == 1 && $sequences[0] == null ) {
            $temp = $game->Roll[0];
            $game->Roll[0] = $game->Roll[1];
            $game->Roll[1] = $temp;
            $this->_GenerateMovesSequence( $sequences, $moves, 0, $game );
        }
        
        // If there are move sequences with all moves not null, remove sequences that has some moves null.
        // (rule of backgammon that you have to use all dice if you can)
        $emptyMoves = $sequences->filter(
            function( $item ) {
                return $item == null || empty( $item );
            }
        );
        
        if ( $emptyMoves->count() ) {
            $sequences = new ArrayCollection(
                \array_values( \array_diff( $sequences->toArray(), $emptyMoves->toArray() ) )
            );
        }
        
        return $sequences;
    }
    
    public function AcceptDoubling(): bool
    {
        if ( ! $this->EngineGame->PlayersPassed() ) {
            return true;
        }
            
        $myScore = $this->Evaluate( $this->EngineGame->CurrentPlayer, $this->EngineGame );
        $oponent = $this->EngineGame->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        $otherScore = $this->Evaluate( $oponent, $this->EngineGame );
        
        $oppPips = $this->EngineGame->CurrentPlayer == PlayerColor::White ?
        $this->EngineGame->BlackPlayer->PointsLeft :
        $this->EngineGame->WhitePlayer->PointsLeft;
        
        $k = ( $myScore - $otherScore ) / $oppPips;
        
        return $k > -0.25; // Just my best guess
    }

    private function ToLocalSequence( array $sequence, Game $game ): Collection
    {
        $moves = new ArrayCollection();
        for ( $i = 0; $i < count( $sequence ); $i++ ) {
            if ( $sequence[$i] != null ) {
                $move   = new Move();
                $move->From = $game->Points[$sequence[$i]->From->BlackNumber];
                $move->To = $game->Points[$sequence[$i]->To->BlackNumber];
                $move->Color = $sequence[$i]->Color;
                    
                $moves[] = $move;
            }
        }
        
        return $moves;
    }

    private function DoSequence( Collection $sequence, Game $game ): Collection
    {
        $hits = new ArrayCollection();
        foreach ( $sequence as $move ) {
            if ( $move == null ) {
                continue;
            }
            $hit = $game->MakeMove( $move );
            $hits[] = $hit;
        }
        $game->SwitchPlayer();
        
        return $hits;
    }

    private function UndoSequence( Collection $sequence, Collection $hits, Game $game ): void
    {
        $game->SwitchPlayer();

        for ( $i = $sequence->count() - 1; $i >= 0; $i-- ) {
            if ( $sequence[$i] != null ) {
                $lastHit    = $hits->last();
                $hits->removeElement( $lastHit );
                
                $game->UndoMove( $sequence[$i], $lastHit );
            }
        }
    }

    private function EvaluatePoints( PlayerColor $myColor, Game $game ): float
    {
        if ( $myColor == PlayerColor::White ) {
            // Higher score for white when few checkers and black has many checkers left
            return $game->BlackPlayer->PointsLeft - $game->WhitePlayer->PointsLeft;
        } else {
            return $game->WhitePlayer->PointsLeft - $game->BlackPlayer->PointsLeft;
        }
    }

    private function EvaluateCheckers( PlayerColor $myColor, Game $game ): float
    {
        $score = 0;
        $inBlock = false;
        $blockCount = 0; // consequtive blocks
        $bt = $this->Configuration->BlotsThreshold;
        $bf = $this->Configuration->BlotsFactor;
        $bfp = $this->Configuration->BlotsFactorPassed;
        $cbf = $this->Configuration->ConnectedBlocksFactor;
        $bps = $this->Configuration->BlockedPointScore;

        $other = $myColor == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        // Oponents checker closest to their bar. Relative to my point numbers.
        $opponentMax = $game->Points->filter(
            function( $entry ) use ( $other ) {
                $checker = $entry->Checkers->first();
                
                return $checker && $checker->Color == $other;
            }
        )->last();
        
        $myMin = $game->Points->filter(
            function( $entry ) use ( $myColor ) {
                $checker = $entry->Checkers->first();
                
                return $checker && $checker->Color == $myColor;
            }
        )->last();
        
        $allPassed = true;

        if ( $myMin->GetNumber( $myColor ) < $opponentMax->GetNumber( $other ) ) {
            for ( $i = 1; $i < 25; $i++ ) {
                // It is important to reverse looping for white
                $point = $game->Points[$i];
                if ( $myColor == PlayerColor::White ) {
                    $point = $game->Points[25 - $i];
                }

                $pointNo = $point->GetNumber( $myColor );

                // If all opponents checkers has passed this point, blots are not as bad
                $allPassed = $pointNo > $opponentMax->GetNumber( $other );

                if ( $point->Block( $myColor ) ) {
                    if ( $inBlock ) {
                        $blockCount++;
                    } else {
                        $blockCount = 1; // Start of blocks
                    }
                    $inBlock = true;
                } else { // not a blocked point
                    if ( $inBlock ) {
                        $score += \pow( $blockCount * $bps, $cbf );
                        $blockCount = 0;
                    }
                    $inBlock = false;
                    if ( $point->Blot( $myColor ) && $point->GetNumber( $myColor ) > $bt ) {
                        $score -= $point->GetNumber( $myColor ) / ( $allPassed ? $bfp : $bf );
                    }
                }
            } // end of loop

            if ( $inBlock ) {
                // the last point
                $score += \pow( $blockCount * $bps, $cbf );
            }
            if ( $allPassed ) {
                $score += $this->EvaluatePoints( $myColor, $game ) * $this->Configuration->RunOrBlockFactor;
            }
        } else {
            // When both players has passed each other it is just better to move to home board and then bear off
            $score += $game->GetHome( $myColor )->Checkers->count() * 100;
            $score += $game->Points->filter(
                function( $entry ) use ( $myColor ) {
                    return $entry->GetNumber( $myColor ) > 18;
                }
            )->count() * 50;
        }

        return $score;
    }

    //Get the average score for current player rolling all possible combinations
    private function ProbabilityScore( PlayerColor $myColor, Game $game ): float
    {
        $allDiceRoll = $this->AllRolls();
        $scores = [];
        $oponent = $myColor == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        foreach ( $allDiceRoll as $roll ) {
            $game->FakeRoll( $roll['dice1'], $roll['dice2'] );
            $bestScore = - PHP_FLOAT_MAX;
            $seqs = $this->GenerateMovesSequence( $game );
            foreach ( $seqs as $s ) {
                $hits = $this->DoSequence( $s, $game );
                $score = $this->EvaluatePoints( $myColor, $game ) + $this->EvaluateCheckers( $myColor, $game );
                $score -= $this->EvaluateCheckers( $oponent, $game );
                if ( $score > $bestScore ) {
                    $bestScore = $score;
                }
                $this->UndoSequence( $s, $hits, $game );
            }
            $m = $roll['dice1'] == $roll['dice2'] ? 1 : 2; // dice roll with not same value on dices are twice as probable. 2 / 36, vs 1 / 36
            if ( ! $seqs->isEmpty() ) {
                $scores[]   = $bestScore * $m;
            }
            // Get best score of each roll, and make an average
            // some rolls are more probable, multiply them
            // some rolls will be blocked or partially blocked
        }
        if ( ! \count( $scores ) ) {
            return -100000; // If player cant move, shes blocked. Thats bad.
        }
        
        return \array_sum( $scores ) / \count( $scores );
    }

    private function AllRolls(): array
    {
        if ( $this->$_allRolls != null )
            return $this->$_allRolls;
        
        $list = [];
        for ( $d1 = 1; $d1 < 7; $d1++ ) {
            for ( $d2 = 1; $d2 < 7; $d2++ ) {
                
                if ( ! \array_key_exists( $d1 . '_' . $d2, $list ) && ! \array_key_exists( $d2 . '_' . $d1, $list ) ) {
                    $list[$d1 . '_' . $d2] = [$d1, $d2];
                }
            }
        }
        $this->$_allRolls = \array_values( $list );
        
        return $this->$_allRolls;
    }

    private function _GenerateMovesSequence( Collection &$sequences, array $moves, int $diceIndex, Game $game ): void
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
            if ( $fromPointNo == 25 )
                continue;
            
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
                if ( ! isset( $moves[$diceIndex] ) || $moves[$diceIndex] == null ) {
                    $moves[$diceIndex] = $move;
                } else { // a move is already generated for this dice in this sequence. branch off a new.
                    $newMoves = [];
                    for ( $i = 0; $i < $diceIndex; $i++ ) {
                        $newMoves[] = $moves[$i];
                    }
                    $newMoves[$diceIndex] = $move;
                    
                    // For last checker identical sequences are omitted
                    if ( $diceIndex < $game->Roll->count() - 1 ) { // || ! $sequences->ContainsEntryWithAll( $newMoves ) 
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
                // The furthest away checker can be moved beyond home
                $currentPlayerPoints = $game->Points->filter(
                    function( $entry ) use ( $game ) {
                        return $entry->Checkers->filter(
                            function( $entry ) use ( $game ) {
                                return $entry && $entry->Color === $game->CurrentPlayer;
                            }
                        );
                    }
                );
                
                $orderedCurrentPlayerPoints = $this->orderPlayerPoints( $currentPlayerPoints, $game );
                $minPoint = $orderedCurrentPlayerPoints->first()->GetNumber( $game->CurrentPlayer );
                
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
                    
                    if ( $moves[$diceIndex] == null ) {
                        $moves[$diceIndex] = $move;
                    } else {
                        $newMoves = new ArrayCollection( $moves->toArray() );
                        $newMoves[$diceIndex] = $move;
                        // For last checker identical sequences are omitted
                        if ( $diceIndex < $game->Roll->count() - 1 ) { // || ! $sequences->ContainsEntryWithAll( $newMoves ) 
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

    private function Evaluate( PlayerColor $color, Game $game ): float
    {
        $score = $this->EvaluatePoints( $color, $game ) + $this->EvaluateCheckers( $color, $game );
        return $score;
    }
}
