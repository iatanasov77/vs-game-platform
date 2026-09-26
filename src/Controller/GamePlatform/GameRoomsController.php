<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Vankosoft\ApplicationBundle\Component\Status;

class GameRoomsController extends AbstractController
{
    /** @var RepositoryInterface */
    private $gcRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gcRepository
    ) {
        $this->doctrine     = $doctrine;
        $this->gcRepository = $gcRepository;
    }
    
    public function index( Request $request, PaginatorInterface $paginator ): Response
    {
        $paginatorItems = $this->gcRepository->findBy( [], ['updatedAt' => 'DESC'] );
        $eooms          = $paginator->paginate(
            $paginatorItems,
            $request->query->getInt( 'page', 1 ) /*page number*/,
            10 /*limit per page*/
        );
        
        return $this->render( 'Pages/GameRooms/index.html.twig', [
            'gameRooms' => $eooms,
        ]);
    }
    
    public function clearGameRooms( Request $request ): Response
    {
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Rooms Cleared !!!',
        ]);
    }
    
    public function deleteGameRoom( $id, Request $request ): Response
    {
        $em     = $this->doctrine->getManager();
        $room   = $this->gcRepository->find( $id );
        
        $em->remove( $room );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Room Deleted !!!',
        ]);
    }
}