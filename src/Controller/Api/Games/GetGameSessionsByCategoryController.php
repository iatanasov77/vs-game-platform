<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use App\Component\GameRoomObjectTrait;
use App\Component\GamePlatform;
use App\Component\GameTeam;
use App\Component\Logger\MercureLogger;
use App\Entity\Game;
use App\Entity\GamePlay;

class GetGameSessionsByCategoryController extends AbstractController
{
    use GameRoomObjectTrait;
    
    /** @var MercureLogger */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var RepositoryInterface */
    private $gamesCategoryRepository;
    
    /** @var RepositoryInterface */
    private $gameSessionsRepository;
    
    /** @var GameTeam */
    private $gameTeamService;
    
    public function __construct(
        MercureLogger $logger,
        SerializerInterface $serializer,
        RepositoryInterface $gamesCategoryRepository,
        RepositoryInterface $gameSessionsRepository,
        GameTeam $gameTeamService
    ) {
        $this->logger                   = $logger;
        $this->serializer               = $serializer;
        $this->gamesCategoryRepository  = $gamesCategoryRepository;
        $this->gameSessionsRepository   = $gameSessionsRepository;
        $this->gameTeamService          = $gameTeamService;
    }
    
    public function index( $categorySlug, Request $request ): JsonResponse
    {
        $category = $this->gamesCategoryRepository->findByTaxonCode( $categorySlug );
        
        $response = [];
        foreach ( $category->getGames() as $game ) {
            $response = \array_merge( $response, $this->_getRooms( $game ) );
        }
        
        return new JsonResponse( $response );
    }
    
    private function _getRooms( Game $game ): array
    {
        $sessions   = $this->gameSessionsRepository->findBy( ['game' => $game] );
        
        $rooms = [];
        foreach ( $sessions as $room ) {
            $rooms[] = $this->createResponseBody( $room, $this->_getRoomTeams( $room ) );
        }
        
        return $rooms;
    }
    
    private function _getRoomTeams( GamePlay $room ): array
    {
        $teams = $this->gameTeamService->createGameTeam( $room );
        
        $teamsResponse = [];
        foreach ( $teams as $team ) {
            $teamsResponse[] = $team;
        }
        
        return $teamsResponse;
    }
}