<?php namespace App\EventListener;

use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class GameEndedListener
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gamePlayRepository
    ) {
        $this->doctrine             = $doctrine;
        $this->gamePlayRepository   = $gamePlayRepository;
    }
    
    public function onGameEnded( GameEndedEvent $event ): void
    {
        $gameDto    = $event->getSubject();
    }
}