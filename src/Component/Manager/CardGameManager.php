<?php namespace App\Component\Manager;

use App\Component\Manager\CardGame\RoundResult;
use App\Component\Type\PlayerPosition;

use App\Component\Manager\GameMechanics\RoundManager;

abstract class CardGameManager extends AbstractGameManager
{
    use RoundManager;
}
