<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Rules\CardGame\Game;

/**
 * BelotGameEngine in C#: https://github.com/NikolayIT/BelotGameEngine
 */
class BridgeBeloteEngine extends Engine
{
    protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void
    {
        
    }
}
