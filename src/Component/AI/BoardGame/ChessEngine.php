<?php namespace App\Component\AI\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;
use App\Component\Rules\BoardGame\Game;
use App\Component\Rules\BoardGame\Move;
use App\Component\Manager\AbstractGameManager;

class ChessEngine extends Engine
{
    protected function _GenerateMovesSequence( Collection &$sequences, Collection &$moves, int $diceIndex, Game $game ): void
    {
        
    }
}
