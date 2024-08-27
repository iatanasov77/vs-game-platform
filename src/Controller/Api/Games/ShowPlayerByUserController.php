<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;

class ShowPlayerByUserController extends AbstractController
{
    /** @var RepositoryInterface */
    private $usersRepository;
    
    public function __construct(
        RepositoryInterface $usersRepository
    ) {
        $this->usersRepository  = $usersRepository;
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
        
        $playerData   = [
            'id'        => $player->getId(),
            'name'      => $player->getName(),
            'type'      => $player->getType(),
            'connected' => true,
        ];
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $playerData,
        ]);
    }
}