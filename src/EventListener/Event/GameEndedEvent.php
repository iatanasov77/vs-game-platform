<?php namespace App\EventListener\Event;

use App\Component\Manager\GameManagerInterface;

/**
 * MANUAL: https://q.agency/blog/custom-events-with-symfony5/
 */
final class GameEndedEvent
{
    public const NAME   = 'app.game_ended';
    
    /** @var GameManagerInterface */
    private $sender;
    
    public function __construct( GameManagerInterface $sender )
    {
        $this->sender = $sender;
    }
    
    public function getSender(): GameManagerInterface
    {
        return $this->sender;
    }
}
