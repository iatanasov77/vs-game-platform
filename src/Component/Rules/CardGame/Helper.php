<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Manager\AbstractGameManager;

trait Helper
{
    protected function sortCards( Collection $cards ): Collection
    {
        $cardsIterator  = $cards->getIterator();
        $cardsIterator->uasort( function ( $a, $b ) {
            return $a->Type->value <=> $b->Type->value;
        });
            
        return new ArrayCollection( \iterator_to_array( $cardsIterator ) );
    }
    
    protected function OrderTrickActionsByCardNoTrumpOrder( Collection $trickActions, string $direction ): Collection
    {
        $trickActionsIterator  = $trickActions->getIterator();
        $trickActionsIterator->uasort( function ( $a, $b ) {
            return $direction == AbstractGameManager::COLLECTION_ORDER_DESC ?
                $b->Card->NoTrumpOrder <=> $a->Card->NoTrumpOrder :
                $a->Card->NoTrumpOrder <=> $b->Card->NoTrumpOrder
            ;
        });
            
        return new ArrayCollection( \iterator_to_array( $trickActionsIterator ) );
    }
}
