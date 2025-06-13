<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

class BackgammonNormalGame extends Game
{   
    public function SwitchPlayer(): void
    {
        $this->CurrentPlayer = $this->OtherPlayer();
    }
    
    public function OtherPlayer(): PlayerColor
    {
        return $this->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
    }
    
    protected function AtHomeAndOtherAtBar(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 2, PlayerColor::Black, 22 );
        $this->AddCheckers( 5, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::Black, 25 );
        
        $this->AddCheckers( 2, PlayerColor::White, 19 );
        $this->AddCheckers( 2, PlayerColor::White, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        $this->AddCheckers( 2, PlayerColor::White, 22 );
        $this->AddCheckers( 2, PlayerColor::White, 23 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 0 );
        
    }
    
    protected function OneMoveToVictory(): void
    {
        //Only one move to victory
        $this->AddCheckers( 14, PlayerColor::Black, 25 );
        $this->AddCheckers( 14, PlayerColor::White, 25 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 24 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
    }
    
    protected function DebugBlocked(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 0 );
        $this->AddCheckers( 1, PlayerColor::White, 0 );
    }
    
    protected function DebugBearingOff(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 19 );
        $this->AddCheckers( 1, PlayerColor::White, 19 );
    }
    
    public function ClearCheckers(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
    }
    
    public function IsBearingOff( PlayerColor $color ): bool
    {
        // Points that have checkers with the color asked all have higher number than 18
        $colorCheckers  = $this->Points->filter(
            function( $entry ) use ( $color ) {
                $askedColor = false;
                
                foreach ( $entry->Checkers as $checker ) {
                    $askedColor = $checker ? $checker->Color == $color : false;
                }
                
                return $askedColor;
            }
        )->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) < 19;
            }
        );
        
        return $colorCheckers->isEmpty();
    }
    
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
    
    public function GetHome( PlayerColor $color ): Point
    {
        return $this->Points->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) == 25;
            }
        )->first();
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
        
        /*
        $this->logger->log( 'All Game Points: ' . \print_r( $this->Points->toArray(), true ) , 'GenerateMoves' );
        $this->logger->log( '', 'GenerateMoves' );
        $this->logger->log( '', 'GenerateMoves' );
        $this->logger->log( 'Current Player: ' . $currentPlayer->value , 'GenerateMoves' );
        $this->logger->log( 'Player Bar: ' . \print_r( $bar->toArray(), true ) , 'GenerateMoves' );
        $this->logger->log( 'Player Bar Has Checkers: ' . $barHasCheckers , 'GenerateMoves' );
        $this->logger->log( '', 'GenerateMoves' );
        $this->logger->log( '', 'GenerateMoves' );
        */
        
        $diceCounter = 0;
        foreach ( $this->getRollOrderByDescending() as $dice ) {
            $diceCounter++;
            
            if ( $dice->Used ) {
                continue;
            }
            $dice->Used = true;
            
            $points = $barHasCheckers ? $bar : $this->getPointsOrdered( $currentPlayer );
            /*
            $this->logger->log( 'Ordered Points for Current Player: ' . \print_r( $this->Points->toArray(), true ) , 'GenerateMoves' );
            $this->logger->log( '', 'GenerateMoves' );
            $this->logger->log( '', 'GenerateMoves' );
            */
            
            $pointsCounter = 0;
            foreach ( $points as $fromPoint ) {
                $pointsCounter++;
                
                $fromPointNo = $fromPoint->GetNumber( $currentPlayer );
                $this->logger->log( 'From Point Number: ' . $fromPointNo , 'GenerateMoves' );
                if ( $fromPointNo == 25 ) {
                    continue;
                }
                
                $shouldPointTo  = $dice->Value + $fromPointNo;
                $this->logger->log( 'To Point Number: ' . $shouldPointTo , 'GenerateMoves' );
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
                
                /*
                $availableMoves = $moves->filter(
                    function( $entry ) use ( $fromPoint, $toPoint ) {
                        return $entry->From == $fromPoint && $entry->To == $toPoint;
                    }
                );
                $this->logger->log( "Has Move: " . ( $hasMove ? 'true' : 'false' ), 'GenerateMoves' );
                $this->logger->log( 'Available Moves: ' . \print_r( $availableMoves->toArray(), true ) , 'GenerateMoves' );
                */
                
                // no creation of bearing off moves here. See next block.
                if (
                    $toPoint != null &&
                    $toPoint->IsOpen( $currentPlayer ) &&
                    ! $hasMove &&
                    ! $toPoint->IsHome( $currentPlayer )
                ) {
                    $move = new Move();
                    $move->Color = $currentPlayer;
                    $move->From = $fromPoint;
                    $move->To = $toPoint;
                    
                    // $this->logger->log( "First Calling MakeMove: diceCounter: {$diceCounter} pointsCounter: {$pointsCounter}", 'GenerateMoves' );
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
                        $move = new Move();
                        $move->Color = $this->CurrentPlayer;
                        $move->From = $fromPoint;
                        $move->To = $toPoint;
                        
                        // $this->logger->log( "Second Calling MakeMove: diceCounter: {$diceCounter} pointsCounter: {$pointsCounter}", 'GenerateMoves' );
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
    
    public function MakeMove( Move $move ): ?Checker
    {
        // [VankoSoft] My Condition to Prevent an Exception
        if ( $move->From->Checkers->count() == 0 ) {
            return null;
        }
        
        $this->logger->log( "MyDebug MakeMove: " . print_r( $move, true ), 'GamePlay' );
        
        $checker = $move->From->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry->Color == $move->Color;
            }
        )->first();
        
        if ( $checker == null ) {
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
                return $entry->Color == $checker->Color;
            }
        )->first();
        if ( $hit != null ) {
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
        
        return $hit;
    }
    
    public function UndoMove( Move $move, ?Checker $hitChecker ): void
    {
        $checker = $move->To->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry->Color == $move->Color;
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
    
    public function getPointsOrdered( $currentPlayer ): Collection
    {
        $points = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                foreach ( $entry->Checkers as $checker ) {
                    return $checker->Color == $currentPlayer;
                }
            }
        );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $a->GetNumber( $currentPlayer ) <=> $b->GetNumber( $currentPlayer );
        });
        
        $orderedPoints  = new ArrayCollection( \iterator_to_array( $pointsIterator ) );
        $this->logger->log( 'Ordered Points: ' . \print_r( $orderedPoints, true ), 'GamePlay' );
        
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
    
    public function ReallyStarted(): bool
    {
        return $this->BlackPlayer->FirstMoveMade && $this->WhitePlayer->FirstMoveMade;
    }
}
