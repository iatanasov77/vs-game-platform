<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;

class GetGameVariantsController extends AbstractController
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
        
        $variants   = [];
        foreach ( $game->getGameVariants() as $variant ) {
            $variants[] =  [
                'id'    => $variant->getId(),
                'slug'  => $variant->getSlug(),
                'title' => $variant->getTitle(),
                'url'   => $variant->getGameUrl(),
                'rooms' => [],
            ];
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $variants,
        ]);
    }
}