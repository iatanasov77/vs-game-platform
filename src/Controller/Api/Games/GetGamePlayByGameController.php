<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;

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
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $rooms      = $this->gameSessionsRepository->findBy( ['game' => $game] );
        $response   = [];
        
        foreach ( $rooms as $room ) {
            $response[] = [
                'id'        => $room->getId(),
                'guid'      => $room->getGuid(),
                'name'      => $room->getGuid(),
                'players'   => $room->getGamePlayers(),
                
                'isPlaying' => $room->isActive(),
            ];
        }
        
        return new JsonResponse( $response );
    }
}