<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerColor;
use App\Component\GameService;
use App\Component\Manager\AbstractGameManager;

trait Helper
{
    protected function orderAllGames( string $gameCode, string $direction ): Collection
    {
        $gamesIterator = $this->AllGames->filter(
            function( $entry ) use ( $gameCode ) {
                return $entry->GameCode === $gameCode;
            }
        )->getIterator();
        
        $gamesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->Created <=> $b->Created :
                $b->Created <=> $a->Created
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $gamesIterator ) );
    }
    
    protected function getPointsForPlayer( PlayerColor $currentPlayer, Game $game ): Collection
    {
        //$this->logger->debug( $game->Points , 'BeforeFilteringPoints.txt' );
        $points = $game->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->Checkers->first() && 
                    $entry->Checkers->first()->Color === $currentPlayer &&
                    $entry->Checkers->last()->Color === $currentPlayer // Check For Tapa
                ;
            }
        );
        //$this->logger->debug( $points , 'BeforeOrderingPoints.txt' );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $a->GetNumber( $currentPlayer ) <=> $b->GetNumber( $currentPlayer );
        });
        
        return new ArrayCollection( \iterator_to_array( $pointsIterator ) );
    }
    
    /*
     * Points.Where(
     *     p => p.Checkers.Any( c => c.Color == CurrentPlayer )
     * ).OrderBy(
     *     p => p.GetNumber( CurrentPlayer )
     * ).First().GetNumber( CurrentPlayer );
     */
    protected function calcMinPoint( Collection $gamePoints, PlayerColor $currentPlayer ): int
    {
        $points  = $gamePoints->filter(
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
            // The Right Ascending Ordering
            return $a->GetNumber( $currentPlayer ) <=> $b->GetNumber( $currentPlayer );
        });
            
        return ( new ArrayCollection( \iterator_to_array( $pointsIterator ) ) )->first()->GetNumber( $currentPlayer );
    }
    
    protected function getMovesOrderedByFromBlackNumber( Collection $moves, string $direction ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->From->BlackNumber <=> $b->From->BlackNumber :
                $b->From->BlackNumber <=> $a->From->BlackNumber
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    protected function getMovesOrderedByFromWhiteNumber( Collection $moves, string $direction ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->From->WhiteNumber <=> $b->From->WhiteNumber :
                $b->From->WhiteNumber <=> $a->From->WhiteNumber
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    protected function getMovesOrdered( Collection $moves, string $direction ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->Value <=> $b->Value :
                $b->Value <=> $a->Value
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    protected function getRollOrdered( string $direction ): Collection
    {
        $dicesIterator  = $this->Roll->getIterator();
        $dicesIterator->uasort( function ( $a, $b ) use ( $direction ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->Value <=> $b->Value :
                $b->Value <=> $a->Value
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $dicesIterator ) );
    }
    
    protected function orderPlayerPoints( Collection $currentPlayerPoints, Game $game, string $direction ): Collection
    {
        $playerPointsIterator   = $currentPlayerPoints->getIterator();
        $playerPointsIterator->uasort( function ( $a, $b ) use ( $direction, $game ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_ASC ?
                $a->GetNumber( $game->CurrentPlayer ) <=> $b->GetNumber( $game->CurrentPlayer ) :
                $b->GetNumber( $game->CurrentPlayer ) <=> $a->GetNumber( $game->CurrentPlayer )
            ;
        });
        
        return new ArrayCollection( \iterator_to_array( $playerPointsIterator ) );
    }
    
    protected function ContainsEntryWithAll( Collection $listOfList, Collection $match ): bool
    {
        // searching for a list entry that contains all entries in match
        foreach ( $listOfList as $list ) {
            $hasMove = true;
            foreach ( $match as $mv ) {
                $moveFound = $list->filter(
                    function( $entry ) use ( $mv  ) {
                        return $mv != null && 
                                $entry != null &&
                                $entry->From == $mv->From &&
                                $entry->To == $mv->To &&
                                $entry->Color == $mv->Color;
                    }
                );
                
                if ( $moveFound->isEmpty() ) {
                    $hasMove = false;
                    break;
                }
            }
            
            if ( $hasMove ) {
                return true;
            }
        }
        
        return false;
    }
}
