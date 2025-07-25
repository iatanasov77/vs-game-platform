<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use App\Component\GameLogger;
use App\Component\GameService;
use App\EventListener\Event\GameEndedEvent;

final class GamesEventListener implements EventSubscriberInterface
{
    /** @var GameLogger */
    private $logger;
    
    /** @var GameService */
    private $gameService;
    
    public function __construct( GameLogger $logger, GameService $service )
    {
        $this->logger       = $logger;
        $this->gameService  = $service;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            GameEndedEvent::NAME => 'onGameEnded',
        ];
    }
    
    public function onGameEnded( GameEndedEvent $event ): void
    {
        $this->logger->log( "GamesEventListener Game Ended !!!", 'GamesEventListener' );
        $this->gameService->Game_Ended( $event->getSender() );
    }
}
