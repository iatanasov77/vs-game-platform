<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

class BackgammonNormalGame extends Game
{   
    public function AddCheckers( int $count, PlayerColor $color, int $point ): void
    {
        $checker        = new Checker();
        $checker->Color = $color;
        
        for ( $i = 0; $i < $count; $i++ ) {
            $this->Points->filter(
                function( $entry ) use ( $color, $point ) {
                    return $entry->GetNumber( $color ) == $point;
                }
            )->first()->Checkers[]  = $checker;
        }
        
        //$this->logger->debug( $this->Points, 'PointsAddCheckers.txt' );
    }
    
    public function GenerateMoves(): array
    {
        $moves = new ArrayCollection();
        $this->_GenerateMoves( $moves );
        
        // Making sure both dice are played
        if ( $moves->NextMoves->count() ) {
            $moves = $moves->filter(
                function( $entry ) {
                    return $entry->NextMoves->count() > 0;
                }
            )->toArray();
        } else if ( $moves->count() ) {
            // All moves have zero next move in this block
            // Only one dice can be use and it must be the one with highest value
            
            $currentPlayer  = $this->CurrentPlayer;
            $this->logger->log( 'MyDebug CurrentPlayer: ' . \print_r( $currentPlayer, true ), 'GamePlay' );
            $moves = $moves->filter(
                function( $entry ) use ( $currentPlayer ) {
                    return $entry->To->GetNumber( $currentPlayer ) - $entry->From->GetNumber( $currentPlayer );
                }
            );
            $first = $moves->getMovesOrderByDescending( $moves )->first();
            $moves->clear();
            $moves[]    = $first;
        }
        
        return $moves;
    }
    
    public function MakeMove( Move $move ): ?Checker
    {
        $this->logger->log( "MyDebug MakeMove: " . print_r( $move, true ), 'GamePlay' );
        
        $checker = $move->From->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry && $entry->Color === $move->Color;
            }
        )->last();
            
        if ( $checker == null ) {
            /*
            $this->logger->debug(
                print_r( $move, true ),
                $this->CurrentPlayer === PlayerColor::Black ? 'MakeMove_Black.txt' : 'MakeMove_White.txt'
            );
            */
            
            throw new \RuntimeException( "There should be a checker on this point. Something is very wrong" );
        }
        
        $move->From->Checkers->removeElement( $checker );
        $move->To->Checkers[]   = $checker;
        if ( $move->Color == PlayerColor::Black ) {
            $this->BlackPlayer->PointsLeft -= ( $move->To->BlackNumber - $move->From->BlackNumber);
        } else {
            $this->WhitePlayer->PointsLeft -= ( $move->To->WhiteNumber - $move->From->WhiteNumber );
        }
            
        // Feels wrong that now that own home is same point as opponent bar
        // Todo: Try to change it some day
        $hit = $move->To->IsHome( $move->Color ) ? null : $move->To->Checkers->filter(
            function( $entry ) use ( $checker ) {
                return $entry && $entry->Color !== $checker->Color;
            }
        )->first();
            
        if ( $hit ) {
            $move->To->Checkers->removeElement( $hit );
            $bar = $this->Points->filter(
                function( $entry ) {
                    return $entry->GetNumber( $this->OtherPlayer() ) == 0;
                }
            )->first();
                
            $bar->Checkers[]    = $hit;
            if ( $move->Color == PlayerColor::Black ) {
                $this->WhitePlayer->PointsLeft += ( 25 - $move->To->WhiteNumber );
            } else {
                $this->BlackPlayer->PointsLeft += ( 25 - $move->To->BlackNumber );
            }
        }
            
        return $hit ?: null;
    }
    
    public function UndoMove( Move $move, ?Checker $hitChecker ): void
    {
        $checker = $move->To->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry && $entry->Color === $move->Color;
            }
            )->first();
            
            $move->To->Checkers->removeElement( $checker );
            $move->From->Checkers[] = $checker;
            if ( $move->Color == PlayerColor::Black ) {
                $this->BlackPlayer->PointsLeft += ( $move->To->BlackNumber - $move->From->BlackNumber );
            } else {
                $this->WhitePlayer->PointsLeft += ( $move->To->WhiteNumber - $move->From->WhiteNumber );
            }
            
            if ( $hitChecker != null ) {
                $move->To->Checkers[]   = $hitChecker;
                $bar = $this->Points->filter(
                    function( $entry ) {
                        return $entry->GetNumber( $this->OtherPlayer() ) == 0;
                    }
                    )->first();
                    $bar->Checkers->removeElement( $hitChecker );
                    if ( $move->Color == PlayerColor::Black ) {
                        $this->WhitePlayer->PointsLeft -= ( 25 - $move->To->WhiteNumber );
                    } else {
                        $this->BlackPlayer->PointsLeft -= ( 25 - $move->To->BlackNumber );
                    }
            }
    }
    
    public function getRollOrderByDescending(): Collection
    {
        $dicesIterator  = $this->Roll->getIterator();
        $dicesIterator->uasort( function ( $a, $b ) {
            return $a->Value <=> $b->Value;
        });
            
            return new ArrayCollection( \iterator_to_array( $dicesIterator ) );
    }
    
    public function getMovesOrderByDescending( Collection $moves ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) {
            return $a->Value <=> $b->Value;
        });
            
            return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    public function getPointsOrdered( PlayerColor $currentPlayer ): Collection
    {
        //$this->logger->debug( $this->Points , 'BeforeFilteringPoints.txt' );
        $points = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->Checkers->first() &&
                $entry->Checkers->first()->Color === $currentPlayer;
            }
        );
        //$this->logger->debug( $points , 'BeforeOrderingPoints.txt' );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $a->GetNumber( $currentPlayer ) <=> $b->GetNumber( $currentPlayer );
        });
        $orderedPoints  = new ArrayCollection( \iterator_to_array( $pointsIterator ) );
        
        return $orderedPoints;
    }
    
    public function calcMinPoint( $currentPlayer )
    {
        $points  = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                $askedColor = false;
                
                foreach ( $entry->Checkers as $checker ) {
                    $askedColor = $checker ? $checker->Color == $currentPlayer : false;
                }
                
                return $askedColor;
            }
        );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $b->GetNumber( $currentPlayer ) <=> $a->GetNumber( $currentPlayer );
        });
            
            return ( new ArrayCollection( \iterator_to_array( $pointsIterator ) ) )->first()->GetNumber( $currentPlayer );
    }
    
    protected function _GenerateMoves( Collection &$moves ): void
    {
        $currentPlayer  = $this->CurrentPlayer;
        $bar = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->GetNumber( $currentPlayer ) == 0;
            }
        );
        $barHasCheckers = $bar->first()->Checkers->exists(
            function( $key, $entry ) use ( $currentPlayer ) {
                return $entry->Color == $currentPlayer;
            }
        );
        
        foreach ( $this->getRollOrderByDescending() as $dice ) {
            if ( $dice->Used ) {
                continue;
            }
            $dice->Used = true;
            
            $points = $barHasCheckers ? $bar : $this->getPointsOrdered( $currentPlayer );
            //$this->logger->debug( $points , 'OrderedPoints.txt' );
            
            foreach ( $points as $fromPoint ) {
                $fromPointNo = $fromPoint->GetNumber( $currentPlayer );
                if ( $fromPointNo == 25 ) {
                    continue;
                }
                $this->logger->log( 'From Point Number: ' . $fromPointNo , 'GenerateMoves' );
                
                $shouldPointTo  = $dice->Value + $fromPointNo;
                $toPoint = $this->Points->filter(
                    function( $entry ) use ( $currentPlayer, $shouldPointTo ) {
                        return $entry->GetNumber( $currentPlayer ) == $shouldPointTo;
                    }
                )->first();
                // $this->logger->log( "GenerateMoves toPoint: " . print_r( $toPoint, true ), 'GenerateMoves' );
                
                $hasMove = $moves->exists(
                    function( $key, $entry ) use ( $fromPoint, $toPoint ) {
                        return $entry->From == $fromPoint && $entry->To == $toPoint;
                    }
                );
                
                // no creation of bearing off moves here. See next block.
                if (
                    $toPoint != null &&
                    $toPoint->IsOpen( $currentPlayer ) &&
                    ! $hasMove &&
                    ! $toPoint->IsHome( $currentPlayer )
                ) {
                    $this->logger->log( 'To Point Number: ' . $shouldPointTo , 'GenerateMoves' );
                    
                    $move = new Move();
                    $move->Color = $currentPlayer;
                    $move->From = $fromPoint;
                    $move->To = $toPoint;
                    
                    $moves[]    = $move;
                    $hit = $this->MakeMove( $move );
                    if ( $hit ) {
                        $this->_GenerateMoves( $move->NextMoves );
                        $this->UndoMove( $move, $hit );
                    }
                }
                    
                if ( $this->IsBearingOff( $currentPlayer ) ) {
                    $this->logger->log( "IsBearingOff !!!", 'GenerateMoves' );
                    
                    // The furthest away checker can be moved beyond home
                    $minPoint = $this->calcMinPoint( $currentPlayer );
                    $toPointNo = $fromPointNo == $minPoint ? \min( 25, $fromPointNo + $dice->Value ) : $fromPointNo + $dice->Value;
                    $toPoint = $this->Points->filter(
                        function( $entry ) use ( $currentPlayer, $toPointNo ) {
                            return $entry->GetNumber( $currentPlayer ) == $toPointNo;
                        }
                    )->first();
                    
                    $hasMove = $moves->exists(
                        function( $key, $entry ) use ( $fromPoint, $toPoint ) {
                            return $entry->From == $fromPoint && $entry->To == $toPoint;
                        }
                    );
                    
                    if (
                        $toPoint != null &&
                        $toPoint->IsOpen( $currentPlayer ) &&
                        ! $hasMove
                    ) {
                        $this->logger->log( 'To Point Number: ' . $shouldPointTo, 'GenerateMoves' );
                        
                        $move = new Move();
                        $move->Color = $this->CurrentPlayer;
                        $move->From = $fromPoint;
                        $move->To = $toPoint;
                        
                        $moves[]    = $move;
                        $hit = $this->MakeMove( $move );
                        if ( $hit ) {
                            $this->_GenerateMoves( $move->NextMoves );
                            $this->UndoMove( $move, $hit );
                        }
                    }
                }
            }
            
            $dice->Used = false;
        }
    }
}
