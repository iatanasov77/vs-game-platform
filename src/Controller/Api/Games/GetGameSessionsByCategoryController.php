<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use App\Component\GamePlatform;
use App\Component\GameTeam;
use App\Component\Logger\MercureLogger;
use App\Entity\Game;
use App\Entity\GamePlay;

class GetGameSessionsByCategoryController extends AbstractController
{
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
            $rooms[] = [
                'id'        => $room->getId(),
                'owner'     => $room->getOwner()->getName(),
                'guid'      => $room->getGuid(),
                'name'      => $room->getGuid(),
                
                'game'      => $this->_getRoomGame( $room ),
                'players'   => $this->_getRoomPlayers( $room ),
                'teams'     => $this->_getRoomTeams( $room ),
                
                'status'    => $room->getStatus(),
                'isPlaying' => $room->getStatus() == GamePlatform::GAME_ROOM_STATUS_PLAYING,
            ];
        }
        
        return $rooms;
    }
    
    private function _getRoomGame( GamePlay $room ): array
    {
        $game   = [
            'id'            => $room->getGame()->getId(),
            'slug'          => $room->getGame()->getSlug(),
            'title'         => $room->getGame()->getTitle(),
            'maxPlayers'    => $room->getGame()->getMaxPlayers(),
        ];
        
        return $game;
    }
    
    private function _getRoomPlayers( GamePlay $room ): array
    {
        $players = [];
        foreach ( $room->getGamePlayers() as $player ) {
            $players[] = [
                'id'    => $player->getId(),
                'type'  => $player->getType(),
                'name'  => $player->getName(),
            ];
        }
        
        return $players;
    }
    
    private function _getRoomTeams( GamePlay $room ): array
    {
        $teams = $this->gameTeamService->createGameTeam( $room );
        /*  
        $json = $this->serializer->serialize( $teams, JsonEncoder::FORMAT );
        $this->logger->log( "Game Room Teams: {$json}", 'GameManager' );
        */
        $teamsResponse = [];
        foreach ( $teams as $team ) {
            $teamsResponse[] = $team;
        }
        
        return $teamsResponse;
    }
}