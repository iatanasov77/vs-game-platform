<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class GetGamePlayController extends AbstractController
{
    /** @var RepositoryInterface */
    private $gameSessionsRepository;
    
    public function __construct( RepositoryInterface $gameSessionsRepository )
    {
        $this->gameSessionsRepository   = $gameSessionsRepository;
    }
    
    public function __invoke( Request $request ): iterable
    {
        $rooms      = $this->gameSessionsRepository->findAll();
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
        
        return $response;
    }
}