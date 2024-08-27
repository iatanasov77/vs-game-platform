<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Entity\GamePlayer;

class AjaxController extends AbstractController
{
    /** @var ManagerRegistry **/
    private $doctrine;
    
    /** @var RepositoryInterface **/
    private $usersRepository;
    
    /** @var Factory **/
    private $playersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $usersRepository,
        Factory $playersFactory
    ) {
        $this->doctrine         = $doctrine;
        $this->usersRepository  = $usersRepository;
        $this->playersFactory   = $playersFactory;
    }
    
    public function createPlayerForUser( $userId, Request $request ): Response
    {
        $user   = $this->usersRepository->find( $userId );
        
        if ( $user->getPlayer() ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => 'User Has Player Already !!!',
            ]);
        }
        
        $player = $this->playersFactory->createNew();
        $player->setUser( $user );
        $player->setType( GamePlayer::TYPE_USER );
        $player->setName( $user->getUsername() );
        
        $em = $this->doctrine->getManager();
        $em->persist( $player );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Player Created !!!',
        ]);
    }
}