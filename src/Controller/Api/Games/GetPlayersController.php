<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;

class GetPlayersController extends AbstractController
{
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    public function __construct(
        RepositoryInterface $playersRepository,
        LiipImagineCacheManager $imagineCacheManager
    ) {
        $this->playersRepository    = $playersRepository;
        $this->imagineCacheManager  = $imagineCacheManager;
    }
    
    public function __invoke( Request $request ): JsonResponse
    {
        $players    = $this->playersRepository->findAll();
        $response   = [];
        
        foreach ( $players as $player ) {
            /*  
            $photoUrl = $this->imagineCacheManager->getBrowserPath(
                $player->getAvatarPath(),
                'users_crud_index_thumb'
            );
            */
            $response[] = [
                'id'        => $player->getId(),
                'type'      => $player->getType(),
                'name'      => $player->getName(),
                'photoUrl'  => '', // $photoUrl,
                'rooms' => [], // $player->getRooms(),
                
                'connected' => (
                    $player->getUser() &&
                    $player->getUser()->getMercureConnection() &&
                    $player->getUser()->getMercureConnection()->isActive()
                ),
            ];
        }
        
        return new JsonResponse( $response );
    }
}
