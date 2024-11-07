<?php namespace App\Component\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Rules\Backgammon\GameFactory as BackgammonRulesFactory;

final class GameManagerFactory
{
    /** @var LoggerInterface */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var BackgammonRulesFactory */
    private $backgammonRulesFactory;
    
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
        SerializerInterface $serializer,
        LiipImagineCacheManager $imagineCacheManager,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        BackgammonRulesFactory $backgammonRulesFactory,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        RepositoryInterface $tempPlayersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $forGold
    ) {
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->imagineCacheManager      = $imagineCacheManager;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->backgammonRulesFactory   = $backgammonRulesFactory;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        $this->forGold                  = $forGold;
    }
    
    public function createWebsocketGameManager(): GameManagerInterface
    {
        return new WebsocketGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->backgammonRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $this->forGold
        );
    }
    
    public function createThruwayGameManager(): GameManagerInterface
    {
        return new ThruwayGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->backgammonRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $this->forGold
        );
    }
    
    public function createZmqGameManager(): GameManagerInterface
    {
        return new ZmqGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->backgammonRulesFactory,
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
