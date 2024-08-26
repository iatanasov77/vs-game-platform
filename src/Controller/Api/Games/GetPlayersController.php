<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Sylius\Component\Resource\Repository\RepositoryInterface;

class GetPlayersController extends AbstractController
{
    /** @var RepositoryInterface */
    private $playersRepository;
    
    public function __construct( RepositoryInterface $playersRepository )
    {
        $this->playersRepository    = $playersRepository;
    }
    
    public function __invoke( Request $request ): iterable
    {
        $players    = $this->playersRepository->findAll();
        $response   = [];
        
        foreach ( $players as $player ) {
            $response[] = [
                'id'    => $player->getId(),
                'type'  => $player->getType(),
                'name'  => $player->getName(),
                'rooms' => [],
                
                'connected' => (
                    $player->getUser() &&
                    $player->getUser()->getMercureConnection() &&
                    $player->getUser()->getMercureConnection()->isActive()
                ),
            ];
        }
        
        return $response;
    }
}
