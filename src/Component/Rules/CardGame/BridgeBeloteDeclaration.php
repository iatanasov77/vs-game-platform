<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BridgeBeloteDeclaration as BridgeBeloteDeclarationType;

class BridgeBeloteDeclaration
{
    public PlayerPosition $position;
    public BridgeBeloteDeclarationType $declaration;
}
