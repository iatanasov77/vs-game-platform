<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\GameLogger;
use App\Component\Rules\GameFactory as GameRulesFactory;

final class GameManagerFactory
{
    /** @var GameLogger */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var GameRulesFactory */
    private $gameRulesFactory;
    
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
    private $EndGameOnTotalThinkTimeElapse;
    
    public function __construct(
        GameLogger $logger,
        SerializerInterface $serializer,
        LiipImagineCacheManager $imagineCacheManager,
        EventDispatcherInterface $eventDispatcher,
        ManagerRegistry $doctrine,
        GameRulesFactory $gameRulesFactory,
        RepositoryInterface $gameRepository,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $playersRepository,
        RepositoryInterface $tempPlayersRepository,
        FactoryInterface $tempPlayersFactory,
        bool $EndGameOnTotalThinkTimeElapse
    ) {
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->imagineCacheManager      = $imagineCacheManager;
        $this->eventDispatcher          = $eventDispatcher;
        $this->doctrine                 = $doctrine;
        $this->gameRulesFactory         = $gameRulesFactory;
        $this->gameRepository           = $gameRepository;
        $this->gamePlayRepository       = $gamePlayRepository;
        $this->gamePlayFactory          = $gamePlayFactory;
        $this->playersRepository        = $playersRepository;
        $this->tempPlayersRepository    = $tempPlayersRepository;
        $this->tempPlayersFactory       = $tempPlayersFactory;
        
        $this->EndGameOnTotalThinkTimeElapse = $EndGameOnTotalThinkTimeElapse;
    }
    
    public function createBackgammonGameManager( bool $forGold, string $gameCode, ?string $gameVariant ): GameManagerInterface
    {
        return new BackgammonGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $forGold,
            $gameCode,
            $gameVariant,
            
            $this->EndGameOnTotalThinkTimeElapse
        );
    }
    
    public function createChessGameManager( bool $forGold, string $gameCode, ?string $gameVariant ): GameManagerInterface
    {
        return new ChessGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $forGold,
            $gameCode,
            $gameVariant,
            
            $this->EndGameOnTotalThinkTimeElapse
        );
    }
    
    public function createBridgeBeloteGameManager( bool $forGold, string $gameCode, ?string $gameVariant ): GameManagerInterface
    {
        return new BridgeBeloteGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $forGold,
            $gameCode,
            $gameVariant,
            
            $this->EndGameOnTotalThinkTimeElapse
        );
    }
    
    public function createContractBridgeGameManager( bool $forGold, string $gameCode, ?string $gameVariant ): GameManagerInterface
    {
        return new ContractBridgeGameManager(
            $this->logger,
            $this->serializer,
            $this->imagineCacheManager,
            $this->eventDispatcher,
            $this->doctrine,
            $this->gameRulesFactory,
            $this->gameRepository,
            $this->gamePlayRepository,
            $this->gamePlayFactory,
            $this->playersRepository,
            $this->tempPlayersRepository,
            $this->tempPlayersFactory,
            $forGold,
            $gameCode,
            $gameVariant,
            
            $this->EndGameOnTotalThinkTimeElapse
        );
    }
}
