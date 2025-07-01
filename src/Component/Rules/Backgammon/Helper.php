<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;
use App\Component\Manager\AbstractGameManager;

trait Helper
{
    protected function getPointsForPlayer( PlayerColor $currentPlayer, Game $game ): Collection
    {
        //$this->logger->debug( $game->Points , 'BeforeFilteringPoints.txt' );
        $points = $game->Points->filter(
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
    
    protected function calcMinPoint( $currentPlayer )
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
    
    protected function getMovesOrderByDescending( Collection $moves ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) {
            return $a->Value <=> $b->Value;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    protected function getRollOrdered( string $direction ): Collection
    {
        $dicesIterator  = $this->Roll->getIterator();
        $dicesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $b->Value <=> $a->Value :
                $a->Value <=> $b->Value
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $dicesIterator ) );
    }
}
