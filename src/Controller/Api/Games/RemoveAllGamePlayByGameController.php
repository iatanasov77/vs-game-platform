<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class RemoveAllGamePlayByGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    /** @var RepositoryInterface */
    private $gameSessionsRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gamesRepository,
        RepositoryInterface $gameSessionsRepository
    ) {
        $this->doctrine                 = $doctrine;
        $this->gamesRepository          = $gamesRepository;
        $this->gameSessionsRepository   = $gameSessionsRepository;
    }
    
    public function index( $gameSlug, Request $request ): JsonResponse
    {
        $em         = $this->doctrine->getManager();
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
            
            $em->remove( $room );
            $em->flush();
        }
        
        return new JsonResponse( $response );
    }
}