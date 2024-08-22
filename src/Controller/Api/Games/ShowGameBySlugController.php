<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;

class ShowGameBySlugController extends AbstractController
{
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    public function __construct(
        RepositoryInterface $gamesRepository
    ) {
        $this->gamesRepository  = $gamesRepository;
    }
    
    public function index( $slug, Request $request ): JsonResponse
    {
        $game  = $this->gamesRepository->findOneBy( ['slug' => $slug] );
        
        $gameData   = [
            'id'    => $game->getId(),
            'slug'  => $game->getSlug(),
            'title' => $game->getTitle(),
            'rooms' => [],
        ];
        foreach ( $game->getRooms() as $room ) {
            $gameData['rooms'][]    = [
                'name'  => $room->getName(),
            ];
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $gameData,
        ]);
    }
}