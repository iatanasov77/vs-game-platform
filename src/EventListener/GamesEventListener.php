<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;
use App\Component\GameLogger;
use App\Component\GameService;
use App\Entity\GamePlatformSettings;

use App\EventListener\Event\GameStartedEvent;
use App\EventListener\Event\GameEndedEvent;

final class GamesEventListener implements EventSubscriberInterface
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var GameLogger */
    private $logger;
    
    /** @var GamePlatformSettings */
    private $gamePlatformSettings;
    
    /** @var GameService */
    private $gameService;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        ApplicationContextInterface $applicationContext,
        GameLogger $logger,
        GameService $service,
        RepositoryInterface $gamePlayRepository
    ) {
        $this->doctrine             = $doctrine;
        $this->logger               = $logger;
        
        $this->gameService          = $service;
        $this->gamePlayRepository   = $gamePlayRepository;
        
        $this->gamePlatformSettings = $applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            GameStartedEvent::NAME  => 'onGameStarted',
            GameEndedEvent::NAME    => 'onGameEnded',
        ];
    }
    
    public function onGameStarted( GameStartedEvent $event ): void
    {
        $this->logger->log( "GamesEventListener Game Started !!!", 'GamesEventListener' );
        
        $gamePlay   = $this->gamePlayRepository->findOneBy( ['guid' => $event->getSender()->Game->Id ] );
        if ( ! $gamePlay || ! $this->gamePlatformSettings->getRemoveLeavedGameSessionsForPlayer() ) {
            return;
        }
        
        $em = $this->doctrine->getManager();
        foreach( $gamePlay->getOwner()->getGameSessions() as $game ) {
            if ( $game->getGuid() != $event->getSender()->Game->Id ) {
                $em->remove( $game );
                $em->flush();
            }
        }
    }
    
    public function onGameEnded( GameEndedEvent $event ): void
    {
        $this->logger->log( "GamesEventListener Game Ended !!!", 'GamesEventListener' );
        $this->gameService->Game_Ended( $event->getSender() );
    }
}
