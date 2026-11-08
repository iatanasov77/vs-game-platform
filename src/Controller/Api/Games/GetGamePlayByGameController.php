<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use App\Component\GameRoomObjectTrait;

class GetGamePlayByGameController extends AbstractController
{
    use GameRoomObjectTrait;
    
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
            $rooms[] = $this->createResponseBody( $room );
        }
        
        return $rooms;
    }
}