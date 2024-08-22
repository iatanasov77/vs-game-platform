<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use App\Entity\Game;

class ShowGameController extends AbstractController
{
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    public function __construct(
        RepositoryInterface $gamesRepository
    ) {
        $this->gamesRepository  = $gamesRepository;
    }
    
    public function __invoke( $id, Request $request ): Game
    {
        $game  = $this->gamesRepository->find( ['id' => $id] );
        
        return $game;
    }
}