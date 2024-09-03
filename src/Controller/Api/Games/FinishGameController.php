<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;

class FinishGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gamePlayRepository,
        HubInterface $hub
    ) {
        $this->doctrine             = $doctrine;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->hub                  = $hub;
    }
    
    public function __invoke( Request $request ): JsonResponse
    {
        
        
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => [
                
            ],
        ]);
    }
}