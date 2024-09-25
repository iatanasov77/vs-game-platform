<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerColor;

/// <summary>
/// Represents one of 24 a triangles where a checker can be placed.
/// 1 to 24.
/// The bar is 0. Beared off is 25.
/// </summary>
class Point
{
    /** @var Collection | Checker[] */
    public $Checkers = new ArrayCollection();
    
    /** @var int */
    public $WhiteNumber;
    
    /** @var int */
    public $BlackNumber;

    public function IsOpen( PlayerColor $myColor ): bool
    {
        //Opponent has less than two checkers on the point.
        //My own home is always open.
        return $this->Checkers->filter(
            function( $entry ) use ( $myColor ) {
                return $entry->Color != $myColor;
            }
        )->count() < 2 || $this->GetNumber( $myColor ) == 25;
    }

    public function GetNumber( PlayerColor $player ): int
    {
        return $player == PlayerColor::Black ? $this->BlackNumber : $this->WhiteNumber;
    }

    public function __toString(): string
    {
        $color = $this->Checkers->count() ? $this->Checkers->First()->Color : "";
        return "{$this->Checkers->count()} {$color} WN = {$this->WhiteNumber}, BN = {$this->BlackNumber}, ";
    }

    public function IsHome( PlayerColor $player ): bool
    {
        return $this->GetNumber( $player ) == 25;
    }

}
