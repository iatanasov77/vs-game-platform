<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\RulesPlayerColor;

class Checker
{
    /** @var RulesPlayerColor */
    public $Color;

    public function __toString(): string
    {
        return $this->Color;
    }
}
