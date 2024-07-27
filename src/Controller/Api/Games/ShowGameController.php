<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;

use App\Entity\Game;

class ShowGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityBridge $vsSecurityBridge,
        RepositoryInterface $gamesRepository
    ) {
        $this->doctrine         = $doctrine;
        $this->vsSecurityBridge = $vsSecurityBridge;
        $this->gamesRepository  = $gamesRepository;
    }
    
    public function __invoke( $id, Request $request ): Game
    {
        //$game  = $this->gamesRepository->findOneBy( ['slug' => $id] );
        $game  = $this->gamesRepository->find( ['id' => $id] );
        
        return $game;
    }
}