<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use App\Component\GameService;
use App\Component\Type\PlayerType;
use App\Component\Type\PlayerPosition;
use App\Component\System\Guid;
use App\Entity\GamePlay;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

class CreateGameRoomController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var GameService */
    private $gamesService;
    
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityBridge $vsSecurityBridge,
        GameService $gamesService,
        
        RepositoryInterface $gamesRepository,
        RepositoryInterface $playersRepository,
        FactoryInterface $gamePlayFactory,
        FactoryInterface $tempPlayersFactory
    ) {
        $this->doctrine             = $doctrine;
        $this->vsSecurityBridge     = $vsSecurityBridge;
        $this->gamesService         = $gamesService;
        
        $this->gamesRepository      = $gamesRepository;
        $this->playersRepository    = $playersRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    /**
     * Creating Game Room for Playing Card Game with Computer
     * @NOTE: This Logic Should be Moved in Game Service
     * 
     * @param string $gameId
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function createGameRoomAction( $gameId, Request $request ): JsonResponse
    {
        $game       = $this->gamesRepository->find( $gameId );
        $gameRoom   = $this->gamePlayFactory->createNew();
        
        $aiPlayer   = $this->playersRepository->findBy([
            'type'  => PlayerType::Computer->value,
            'guid'  => null,
        ])[0];
        
        if ( ! $game ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => 'Cannot Find Requested Game.',
            ]);
        }
        
        if ( ! $aiPlayer ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => 'Cannot Find an AI Player.',
            ]);
        }
        
        $gameRoom->setGame( $game );
        $gameRoom->setGuid( Guid::NewGuid() );
        $this->createGamePlayers( $gameRoom, $aiPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $gameRoom );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $this->createResponseBody( $gameRoom ),
        ]);
    }
    
    private function createGamePlayers( GamePlay &$gameRoom, GamePlayer $aiPlayer ): void
    {
        $currentUser = $this->vsSecurityBridge->getUser();
        
        // First Player
        $gameRoom->addGamePlayer( $this->createGamePlayer( $currentUser->getPlayer(), [
            'name' => $currentUser->getUsername(),
            'position' => PlayerPosition::North->toString(),
        ]));
        
        // Second Player
        $gameRoom->addGamePlayer( $this->createGamePlayer( $aiPlayer, [
            'name' => 'Computer_1',
            'position' => PlayerPosition::East->toString(),
        ]));
        
        // Third Player
        $gameRoom->addGamePlayer( $this->createGamePlayer( $aiPlayer, [
            'name' => 'Computer_2',
            'position' => PlayerPosition::South->toString(),
        ]));
        
        // Fourth Player
        $gameRoom->addGamePlayer( $this->createGamePlayer( $aiPlayer, [
            'name' => 'Computer_3',
            'position' => PlayerPosition::West->toString(),
        ]));
    }
    
    private function createGamePlayer( GamePlayer $basePlayer, array $payerData ): TempPlayer
    {
        $player = $this->tempPlayersFactory->createNew();
        $player->setGuid( Guid::NewGuid() );
        $player->setPlayer( $basePlayer );
        $player->setPosition( $payerData['position'] );
        $player->setName( $payerData['name'] );
        
        return $player;
    }
    
    private function createResponseBody( GamePlay $gameRoom ): array
    {
        $data = [
            'id'    => $gameRoom->getId(),
            'room'  => [
                'id'        => $gameRoom->getId(),
                'players'   => [],
            ],
        ];
        
        foreach ( $gameRoom->getGamePlayers() as $player ) {
            $data['room']['players'][] = [
                'id'            => $player->getGuid(),
                'containerId'   => $player->getPosition(),
                'name'          => $player->getName(),
                'type'          => $player->getType(),
            ];
        }
        
        return $data;
    }
}