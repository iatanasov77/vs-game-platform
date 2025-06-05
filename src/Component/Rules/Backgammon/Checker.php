<?php namespace App\Component\Rules\Backgammon;

use App\Component\Type\PlayerColor;

class Checker
{
    /** @var PlayerColor */
    public $Color;
    
    public function __toString(): string
    {
        $playerColor;
        switch ( $this->PlayerColor->value ) {
            case 0:
                $playerColor = 'Black';
                break;
            case 1:
                $playerColor = 'White';
                break;
            default:
                $playerColor = 'Neither';
        }
        
        return $playerColor;
    }
}
