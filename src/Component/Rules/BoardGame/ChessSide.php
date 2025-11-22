<?php namespace App\Component\Rules\BoardGame;

use App\Component\Type\PlayerColor;

class ChessSide
{
    public function __construct( PlayerColor $type )
    {
        $this->type = $type;
    }
    
    /** @var PlayerColor */
    public $type;
    
    // Return true if the side is white
    public function isWhite(): bool
    {
        return $this->type == PlayerColor::White;
    }
    
    // Return true if the side is black
    public function isBlack(): bool
    {
        return $this->type == PlayerColor::Black;
    }
    
    // Returns the enemy type
    public function Enemy(): PlayerColor
    {
        if ( $this->type == PlayerColor::White ) {
            return PlayerColor::Black;
        } else {
            return PlayerColor::White;
        }
    }
    
    // return true if the other side is of enemy
    public function isEnemy( ChessSide $other ): bool
    {
        return $this->type != $other->type;
    }
}
