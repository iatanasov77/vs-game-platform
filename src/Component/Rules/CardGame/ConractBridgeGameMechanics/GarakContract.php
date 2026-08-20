<?php namespace App\Component\Rules\CardGame\ConractBridgeGameMechanics;

use Garak\Bridge\Auction;

/**
 * Auction is abstract, so the application must provide a concrete implementation:
 */
final class GarakContract extends Auction
{
    public function __toString(): string
    {
        return ( string ) $this->getValue() . ( $this->getTrump() !== null ? $this->getTrump()->value : '' );
    }
}
