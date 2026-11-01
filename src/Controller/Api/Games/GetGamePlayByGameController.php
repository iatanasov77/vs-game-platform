<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use App\Component\GamePlatform;
use App\Entity\GamePlay;

class GetGamePlayByGameController extends AbstractController
{
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    /** @var RepositoryInterface */
    private $gameSessionsRepository;
    
    public function __construct( RepositoryInterface $gamesRepository, RepositoryInterface $gameSessionsRepository )
    {
        $this->gamesRepository          = $gamesRepository;
        $this->gameSessionsRepository   = $gameSessionsRepository;
    }
    
    public function index( $gameSlug, Request $request ): JsonResponse
    {
        $response   = $this->_getRooms( $gameSlug );
        
        return new JsonResponse( $response );
    }
    
    private function _getRooms( $gameSlug ): array
    {
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
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
}