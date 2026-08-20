<?php namespace App\Component\Rules\CardGame\ConractBridgeGameMechanics;

use Garak\Bridge\Player as BaseGarakPlayer;

final class GarakPlayer extends BaseGarakPlayer
{
    public function isEqual( BaseGarakPlayer $player ): bool
    {
        return $this->name == $player->name;
    }
}
