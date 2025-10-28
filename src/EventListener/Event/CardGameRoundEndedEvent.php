<?php namespace App\EventListener\Event;

use App\Component\Rules\GameInterface;

/**
 * MANUAL: https://q.agency/blog/custom-events-with-symfony5/
 */
final class CardGameRoundEndedEvent
{
    public const NAME   = 'app.card_game_round_ended';
    
    /** @var GameInterface */
    private $sender;
    
    public function __construct( GameInterface $sender )
    {
        $this->sender = $sender;
    }
    
    public function getSender(): GameInterface
    {
        return $this->sender;
    }
}
