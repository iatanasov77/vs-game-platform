<?php namespace App\EventListener\WebsocketEvent;

use Symfony\Component\EventDispatcher\GenericEvent;

final class GameEndedEvent extends GenericEvent
{
    public const NAME   = 'app.game_ended';
}
