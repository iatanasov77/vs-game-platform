<?php namespace App\Component\Rules\Backgammon;

use App\Component\Type\PlayerColor;

class Checker
{
    /** @var PlayerColor */
    public $Color;
    
    public function __toString(): string
    {
        return $this->Color;
    }
}
