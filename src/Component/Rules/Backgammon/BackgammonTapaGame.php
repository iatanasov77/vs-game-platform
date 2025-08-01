<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;
use App\Component\Rules\Backgammon\Helper as GameHelper;
use App\Component\Manager\AbstractGameManager;

class BackgammonTapaGame extends Game
{
    use GameHelper;
    
    public function SetStartPosition(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
        
        $this->AddCheckers( 15, PlayerColor::Black, 1 );
        $this->AddCheckers( 15, PlayerColor::White, 1 );
        
        // CloseToVictory();
        // DebugBar();
        //DebugBlocked();
        // DebugBearingOff();
        // AtHomeAndOtherAtBar();
        // AtHomeAndOtherAtBar2();
        // Test();
        // LegalMove();
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
            $this->logger->log( 'CurrentPlayer: ' . \print_r( $currentPlayer, true ), 'GamePlay' );
            $moves = $moves->filter(
                function( $entry ) use ( $currentPlayer ) {
                    return $entry->To->GetNumber( $currentPlayer ) - $entry->From->GetNumber( $currentPlayer );
                }
            );
            $first = $moves->getMovesOrdered( $moves, AbstractGameManager::COLLECTION_ORDER_ASC )->first();
            $moves->clear();
            $moves[] = $first;
        }
        
        return $moves;
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
        
        foreach ( $this->getRollOrdered( AbstractGameManager::COLLECTION_ORDER_DESC ) as $dice ) {
            if ( $dice->Used ) {
                continue;
            }
            $dice->Used = true;
            
            $points = $barHasCheckers ? $bar : $this->getPointsForPlayer( $currentPlayer, $this );
            /*
             $debugFile  = $currentPlayer === PlayerColor::Black ? 'OrderedPoints_Black.txt' : 'OrderedPoints_White.txt';
             $this->logger->debug( $points, $debugFile );
             */
            
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
                    
                    $hit = $this->MakeMove( $move );
                    $this->_GenerateMoves( $move->NextMoves );
                    $this->UndoMove( $move, $hit );
                    
                    $moves[]    = $move;
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
                    
                    //$this->logger->log( "fromPointNo: " . $fromPointNo, 'BearingOff' );
                    //$this->logger->log( "minPoint: " . $minPoint, 'BearingOff' );
                    //$this->logger->log( "toPointNo: " . $toPointNo, 'BearingOff' );
                    
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
                        
                        $hit = $this->MakeMove( $move );
                        $this->_GenerateMoves( $move->NextMoves );
                        $this->UndoMove( $move, $hit );
                        
                        $moves[]    = $move;
                    }
                }
            }
            
            $dice->Used = false;
        }
    }
}
