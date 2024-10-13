<?php namespace App\Component\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

final class GameManagerFactory
{
    /** @var LoggerInterface */
    private $logger;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gameRepository;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var RepositoryInterface */
    private $tempPlayersRepository;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    /** @var bool */
    private $forGold;
    
    public function __construct(
        LoggerInterface $logger,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        RepositoryInterface $tempPlayersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $forGold
    ) {
        $this->logger                   = $logger;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        $this->forGold                  = $forGold;
    }
    
    public function createGameManager(): GameManager
    {
        return new GameManager(
            $this->logger,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $this->forGold
        );
    }
    
    public function createZmqGameManager(): GameManager
    {
        return new ZmqGameManager(
            $this->logger,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $this->forGold
        );
    }
}
