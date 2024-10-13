<?php namespace App\Component\Ai\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerColor;
use App\Component\Rules\Backgammon\Game;
use App\Component\Rules\Backgammon\Move;

class Engine
{
    /** @var Game */
    private $EngineGame;

    /** @var Config */
    private $Configuration;
    
    /** @var (int dice1, int dice2)[] */
    private static $_allRolls = null;
    
    public function __construct( Game $game )
    {
        $this->EngineGame = $game;
        $this->Configuration = Config::Trained();
    }

    public function GetBestMoves(): Collection
    {
        $bestMoveSequence = null;
        $bestScore = - PHP_FLOAT_MAX;
        $allSequences = self::GenerateMovesSequence( $this->EngineGame );

        $oponent = $this->EngineGame->OtherPlayer();
        $myColor = $this->EngineGame->CurrentPlayer;
        $inParallel = 2;

        /**
         * PHP Manual for Parallel: https://www.php.net/manual/en/book.parallel.php
         */
        /*
        $opt = new ParallelOptions { MaxDegreeOfParallelism = inParallel };
        Parallel.ForEach( allSequences, opt, (sequence) =>
        {
            var g = allSequences.IndexOf(sequence) % inParallel;
            var game = EngineGame.Clone();

            var localSequence = ToLocalSequence(sequence, game);

            var hits = DoSequence(localSequence, game);
            var score = 0d;
            if (Configuration.ProbablityScore)
                score = -ProbabilityScore(oponent, game);
            else
                score = EvaluatePoints(myColor, game) + EvaluateCheckers(myColor, game);

            UndoSequence(localSequence, hits, game);
            //Console.WriteLine($"Engine search {s} of {allSequences.Count}\t{score.ToString("0.##")}\t{sequence.BuildString()}");
            if (score > bestScore)
            {
                bestScore = score;
                $bestMoveSequence = sequence;
            }
        });
        */
        
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
                return $a->From->BlackNumber < $b->From->BlackNumber;
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
            return $a->From->WhiteNumber < $b->From->WhiteNumber;
        });
            
        return \iterator_to_array( $bestMoveSequence );
    }

    private function ToLocalSequence( Collection $sequence, Game $game ): Collection
    {
        $moves = new ArrayCollection();
        for ( $i = 0; $i < $sequence->count; $i++ ) {
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

    private static function DoSequence( Collection $sequence, Game $game ): Collection
    {
        $hits = new ArrayCollection();
        foreach ( $sequence as $move ) {
            if ( $move == null )
                continue;
            $hit = $game->MakeMove( $move );
            $hits[] = $hit;
        }
        $game->SwitchPlayer();
        
        return $hits;
    }

    private static function UndoSequence( Collection $sequence, Collection $hits, Game $game ): void
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

    private static function EvaluatePoints( PlayerColor $myColor, Game $game ): float
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
        $opponentColorNumber = $game->Points->filter(
            function( $entry ) use ( $other ) {
                return $entry->Checkers->exists(
                    function( $entry ) use ( $other ) {
                        return $entry->Color == $other;
                    }
                );
            }
        )->pluck( $myColor == PlayerColor::Black ? 'BlackNumber' : 'WhiteNumber' );
        $opponentMax = \max( $opponentColorNumber );
        
        $myColorNumber = $game->Points->filter(
            function( $entry ) use ( $myColor ) {
                return $entry->Checkers->exists(
                    function( $entry ) use ( $myColor ) {
                        return $entry->Color == $myColor;
                    }
                );
            }
        )->pluck( $myColor == PlayerColor::Black ? 'BlackNumber' : 'WhiteNumber' );
        $myMin = \min( $myColorNumber );

        $allPassed = true;

        if ( $myMin < $opponentMax ) {
            for ( $i = 1; $i < 25; $i++ ) {
                // It is important to reverse looping for white
                $point = $game->Points[$i];
                if ( $myColor == PlayerColor::White ) {
                    $point = $game->Points[25 - $i];
                }

                $pointNo = $point->GetNumber( $myColor );

                // If all opponents checkers has passed this point, blots are not as bad
                $allPassed = $pointNo > $opponentMax;

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
                $score += self::EvaluatePoints( $myColor, $game ) * $this->Configuration->RunOrBlockFactor;
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
        $allDiceRoll = self::AllRolls();
        $scores = [];
        $oponent = $myColor == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        foreach ( $allDiceRoll as $roll ) {
            $game->FakeRoll( $roll['dice1'], $roll['dice2'] );
            $bestScore = - PHP_FLOAT_MAX;
            $seqs = self::GenerateMovesSequence( $game );
            foreach ( $seqs as $s ) {
                $hits = self::DoSequence( $s, $game );
                $score = self::EvaluatePoints( $myColor, $game ) + self::EvaluateCheckers( $myColor, $game );
                $score -= self::EvaluateCheckers( $oponent, $game );
                if ( $score > $bestScore ) {
                    $bestScore = $score;
                }
                self::UndoSequence( $s, $hits, $game );
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

    private static function AllRolls(): array
    {
        if ( self::$_allRolls != null )
            return self::$_allRolls;
        
        $list = [];
        for ( $d1 = 1; $d1 < 7; $d1++ ) {
            for ( $d2 = 1; $d2 < 7; $d2++ ) {
                
                if ( ! \array_key_exists( $d1 . '_' . $d2, $list ) && ! \array_key_exists( $d2 . '_' . $d1, $list ) ) {
                    $list[$d1 . '_' . $d2] = [$d1, $d2];
                }
            }
        }
        self::$_allRolls = \array_values( $list );
        
        return self::$_allRolls;
    }

    public static function GenerateMovesSequence( Game $game ): array
    {
        $sequences = [];
        $moves = new ArrayCollection();
        $sequences[]    = $moves;
        self::_GenerateMovesSequence( $sequences, $moves, 0, $game );

        // Special case. Sometimes the first dice is blocked, but can be moved after next dice
        if ( \count( $sequences ) == 1 && $sequences[0] == null )
        {
            $temp = $game->Roll[0];
            $game->Roll[0] = $game->Roll[1];
            $game->Roll[1] = $temp;
            self::_GenerateMovesSequence( $sequences, $moves, 0, $game );
        }

        // If there are move sequences with all moves not null, remove sequences that has some moves null.
        // (rule of backgammon that you have to use all dice if you can)
        $emptyMoves = \array_filter( $sequences, function( $item ) {
            return $item == null || $item->isEmpty();
        });
        if ( \count( $emptyMoves ) ) {
            $sequences = \array_values( \array_diff( $sequences, $emptyMoves ) );
        }
        
        return $sequences;
    }

    private static function _GenerateMovesSequence( array &$sequences, Collection $moves, int $diceIndex, Game $game ): void
    {
        $current = $game->CurrentPlayer;
        $bar = $game->Bars[$current->value];
        $barHasCheckers = $bar->Checkers->filter(
            function( $entry ) use ( $current ) {
                return $entry->Color == $current;
            }
        )->count();
        $dice = $game->Roll[$diceIndex];

        $points = $barHasCheckers ? [ $bar ] : $game->Points->filter(
            function( $entry ) use ( $current ) {
                return $entry->Checkers->exists(
                    function( $entry ) use ( $current ) {
                        return $entry == $current;
                    }
                );
            }
        )->toArray();
            
        // There seems to be a big advantage to evaluate points from lowest number
        // If not reversing here, black will win 60 to 40 with same config
        if ( $game->CurrentPlayer == PlayerColor::White )
            $points = \array_reverse( $points );

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
                $move->Color = game->CurrentPlayer;
                $move->From = $fromPoint;
                $move->To = $toPoint;
                
                //copy and make a new list for first dice
                if ( $moves[$diceIndex] == null ) {
                    $moves[$diceIndex] = $move;
                } else {
                    // a move is already generated for this dice in this sequence. branch off a new.
                    $newMoves = new ArrayCollection( $moves->getValues() );
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
                    self::_GenerateMovesSequence( $sequences, $moves, $diceIndex + 1, $game );
                    $game->UndoMove( $move, $hit );
                }
            } else if ( $game->IsBearingOff( $game->CurrentPlayer ) ) {
                // The furthest away checker can be moved beyond home
                $currentPlayerPoints = $game->Points->filter(
                    function( $entry ) use ( $game ) {
                        return $entry->Checkers->filter(
                            function( $entry ) use ( $game ) {
                                return $entry->Color == $game->CurrentPlayer;
                            }
                        );
                    }
                );
                
                $orderedCurrentPlayerPoints = self::orderPlayerPoints( $currentPlayerPoints, $game );
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
                        self::_GenerateMovesSequence( $sequences, $moves, $diceIndex + 1, $game );
                        $game->UndoMove( $move, $hit );
                    }
                }
            }
        }
    }

    private function Evaluate( PlayerColor $color, Game $game ): float
    {
        $score = self::EvaluatePoints( $color, $game ) + self::EvaluateCheckers( $color, $game );
        return $score;
    }

    public function AcceptDoubling(): bool
    {
        if ( ! $this->EngineGame->PlayersPassed() )
            return true;

        $myScore = $this->Evaluate( $this->EngineGame->CurrentPlayer, $this->EngineGame );
        $oponent = $this->EngineGame->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        $otherScore = $this->Evaluate( $oponent, $this->EngineGame );

        $oppPips = $this->EngineGame->CurrentPlayer == PlayerColor::White ?
            $this->EngineGame->BlackPlayer->PointsLeft :
            $this->EngineGame->WhitePlayer->PointsLeft;

        $k = ( $myScore - $otherScore ) / $oppPips;

        return $k > -0.25; // Just my best guess
    }
    
    private static function orderPlayerPoints( Collection $playerPoints, $game ): Collection
    {
        $pointsIterator  = $playerPoints->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $game ) {
            return $a->GetNumber( $game->CurrentPlayer ) < $b->GetNumber( $game->CurrentPlayer );
        });
            
        return new ArrayCollection( \iterator_to_array( $pointsIterator ) );
    }
}
