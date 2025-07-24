<?php namespace App\Component\AI\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\GameLogger;
use App\Component\Type\PlayerColor;
use App\Component\Rules\Backgammon\Game;
use App\Component\Rules\Backgammon\Move;
use App\Component\Rules\Backgammon\Helper as GameHelper;
use App\Component\Manager\AbstractGameManager;

abstract class Engine
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
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $allSequences->toArray(), true ), 'EngineGenerateMoves' );
        
        $oponent    = $this->EngineGame->OtherPlayer();
        $myColor    = $this->EngineGame->CurrentPlayer;
        
        foreach ( $allSequences as $sequence ) {
            $localSequence = $this->ToLocalSequence( $sequence, $this->EngineGame );
            
            $hits = $this->DoSequence( $localSequence, $this->EngineGame );
            $score = $this->EvaluatePoints( $myColor, $this->EngineGame ) + $this->EvaluateCheckers( $myColor, $this->EngineGame );
            
            $this->UndoSequence( $localSequence, $hits, $this->EngineGame );
            if ( $score > $bestScore ) {
                $bestScore = $score;
                $bestMoveSequence = $sequence;
            }
        }
        
        if ( $bestMoveSequence == null ) {
            $this->logger->debug( $allSequences->toArray(), 'EngineAllSequences.txt' );
            return new ArrayCollection();
        }
        
        //$this->logger->log( 'BestMoves Before Filter: ' . print_r( $bestMoveSequence->toArray(), true ), 'EnginMoves' );
        $bestMoveSequence   = $bestMoveSequence->filter(
            function( $entry ) {
                return $entry != null && ! $entry->isNull();
            }
        );
        
        if ( $myColor == PlayerColor::Black ) {
            return $this->getMovesOrderedByFromBlackNumber( $bestMoveSequence, AbstractGameManager::COLLECTION_ORDER_ASC );
        }
        
        try {
            return $this->getMovesOrderedByFromWhiteNumber( $bestMoveSequence, AbstractGameManager::COLLECTION_ORDER_ASC );
        } catch ( \Exception $e ) {
            $this->logger->log( print_r( $bestMoveSequence->toArray(), true ), 'EngineGenerateMoves' );
            throw $e;
        }
    }
    
    public function GenerateMovesSequence( Game $game ): Collection // List<Move[]>
    {
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $game->Roll->toArray(), true ), 'EngineGenerateMoves' );
        $sequences  = new ArrayCollection();
        $moves      = new ArrayCollection();
        foreach ( $game->Roll as $roll ) {
            $moves[]    = new Move();
        }
        $sequences[]    = $moves;
        
        $this->_GenerateMovesSequence( $sequences, $moves, 0, $game );
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $sequences->toArray(), true ), 'EngineGenerateMoves' );
        
        // Special case. Sometimes the first dice is blocked, but can be moved after next dice
        if ( $sequences->count() == 1 ) {
            $blockedMoves = $sequences[0]->filter(
                function( $item ) {
                    return $item->isNull();
                }
            );
            if ( $blockedMoves->count() ) {
                $this->logger->log( 'Has Blocked Moves. Count: ' . $blockedMoves->count(), 'EngineGenerateMoves' );
                $temp = $game->Roll[0];
                $game->Roll[0] = $game->Roll[1];
                $game->Roll[1] = $temp;
                $this->_GenerateMovesSequence( $sequences, $moves, 0, $game );
            }
        }
        
        // If there are move sequences with all moves not null, remove sequences that has some moves null.
        // (rule of backgammon that you have to use all dice if you can)
        $sequencesWithEmptyMoves = $sequences->filter(
            function( $entry ) {
                return $entry->filter(
                    function( $entry ) {
                        return $entry->isNull();
                    }
                );
            }
        );
        
        if ( $sequences->count() > $sequencesWithEmptyMoves->count() ) {
            $sequences = new ArrayCollection(
                \array_values( \array_diff( $sequences->toArray(), $sequencesWithEmptyMoves->toArray() ) )
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

    abstract protected function _GenerateMovesSequence( Collection &$sequences, Collection &$moves, int $diceIndex, Game $game ): void;
    
    protected function ToLocalSequence( Collection $sequence, Game $game ): Collection
    {
        try {
            $moves = new ArrayCollection();
            for ( $i = 0; $i < count( $sequence ); $i++ ) {
                if ( ! $sequence[$i]->isNull() ) {
                    $move   = new Move();
                    $move->From = $game->Points[$sequence[$i]->From->BlackNumber];
                    $move->To = $game->Points[$sequence[$i]->To->BlackNumber];
                    $move->Color = $sequence[$i]->Color;
                        
                    $moves[] = $move;
                }
            }
        } catch ( \Exception $e ) {
            $this->logger->log( 'Exception at Engine::ToLocalSequence', 'EngineGenerateMoves' );
            $this->logger->log( 'Wrong Sequence: ' . print_r( $sequence->toArray(), true ), 'EngineGenerateMoves' );
            throw $e;
        }
        
        return $moves->count() ? $moves : $sequence;
    }

    protected function DoSequence( Collection $sequence, Game $game ): Collection
    {
        $hits = new ArrayCollection();
        foreach ( $sequence as $move ) {
            if ( $move == null || $move->isNull() ) {
                continue;
            }
            $hit = $game->MakeMove( $move );
            $hits[] = $hit;
        }
        $game->SwitchPlayer();
        
        return $hits;
    }

    protected function UndoSequence( Collection $sequence, Collection $hits, Game $game ): void
    {
        $game->SwitchPlayer();

        for ( $i = $sequence->count() - 1; $i >= 0; $i-- ) {
            if ( $sequence[$i] != null && $hits->count() ) {
                $lastHit    = $hits->last();
                $hits->removeElement( $lastHit );
                
                $game->UndoMove( $sequence[$i], $lastHit );
            }
        }
    }

    protected function EvaluatePoints( PlayerColor $myColor, Game $game ): float
    {
        if ( $myColor == PlayerColor::White ) {
            // Higher score for white when few checkers and black has many checkers left
            return $game->BlackPlayer->PointsLeft - $game->WhitePlayer->PointsLeft;
        } else {
            return $game->WhitePlayer->PointsLeft - $game->BlackPlayer->PointsLeft;
        }
    }

    protected function EvaluateCheckers( PlayerColor $myColor, Game $game ): float
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
    protected function ProbabilityScore( PlayerColor $myColor, Game $game ): float
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

    protected function AllRolls(): array
    {
        if ( $this->_allRolls != null ) {
            return $this->_allRolls;
        }
        
        $list = [];
        for ( $d1 = 1; $d1 < 7; $d1++ ) {
            for ( $d2 = 1; $d2 < 7; $d2++ ) {
                
                if ( ! \array_key_exists( $d1 . '_' . $d2, $list ) && ! \array_key_exists( $d2 . '_' . $d1, $list ) ) {
                    $list[$d1 . '_' . $d2] = [$d1, $d2];
                }
            }
        }
        $this->_allRolls = \array_values( $list );
        
        return $this->_allRolls;
    }

    protected function Evaluate( PlayerColor $color, Game $game ): float
    {
        $score = $this->EvaluatePoints( $color, $game ) + $this->EvaluateCheckers( $color, $game );
        return $score;
    }
}
