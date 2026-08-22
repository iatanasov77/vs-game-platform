<?php namespace App\Component\Rules\CardGame\ConractBridgeGameMechanics;

use Garak\Bridge\Player as BaseGarakPlayer;

final class GarakPlayer extends BaseGarakPlayer
{
    public function __construct( protected string $name, protected int $id )
    {
    }
    
    public function isEqual( BaseGarakPlayer $player ): bool
    {
        return $this->id == $player->id;
    }
}
