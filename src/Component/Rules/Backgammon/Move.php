<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerColor;

class Move
{
    /** @var Point */
    public $From;
    
    /** @var Point */
    public $To;
    
    /** @var PlayerColor */
    public $Color;
    
    /** @var Collection | Move[] */
    public $NextMoves;
    
    public function __construct()
    {
        $this->NextMoves    = new ArrayCollection();
    }
    
    public function __toString(): string
    {
        return "{$this->Color} {$this->From->GetNumber( $this->Color )} -> {$this->To->GetNumber( $this->Color )}";
    }
        
    public function Equals( Move $move ): bool
    {
        return $move->From == $this->From && $move->To == $this->To && $move->Color == $this->Color;
    }
}
