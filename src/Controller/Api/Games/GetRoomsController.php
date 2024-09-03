<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class GetRoomsController extends AbstractController
{
    /** @var RepositoryInterface */
    private $roomsRepository;
    
    public function __construct( RepositoryInterface $roomsRepository )
    {
        $this->roomsRepository    = $roomsRepository;
    }
    
    public function __invoke( Request $request ): iterable
    {
        $rooms      = $this->roomsRepository->findAll();
        $response   = [];
        
        foreach ( $rooms as $room ) {
            $response[] = [
                'id'        => $room->getId(),
                'slug'      => $room->getSlug(),
                'name'      => $room->getName(),
                'players'   => $room->getPlayers(),
                
                'isPlaying' => $room->isPlaying(),
            ];
        }
        
        return $response;
    }
}