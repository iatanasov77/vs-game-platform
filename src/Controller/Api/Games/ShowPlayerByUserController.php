<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use Vankosoft\ApplicationBundle\Component\Status;

class ShowPlayerByUserController extends AbstractController
{
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    public function __construct(
        RepositoryInterface $usersRepository,
        LiipImagineCacheManager $imagineCacheManager
    ) {
        $this->usersRepository      = $usersRepository;
        $this->imagineCacheManager  = $imagineCacheManager;
    }
    
    public function __invoke( $id, Request $request ): JsonResponse
    {
        $user   = $this->usersRepository->find( $id );
        $player = $user->getPlayer();
        
        if ( ! $player ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => 'User Has No Player',
            ]);
        }
        
        $photoUrl = $this->imagineCacheManager->getBrowserPath(
            $player->getAvatarPath(),
            'users_crud_index_thumb'
        );
        
        $playerData   = [
            'id'        => $player->getId(),
            'name'      => $player->getName(),
            'photoUrl'  => $photoUrl,
            'type'      => $player->getType(),
            'connected' => true,
        ];
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $playerData,
        ]);
    }
}