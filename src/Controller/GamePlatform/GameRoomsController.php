<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Knp\Component\Pager\PaginatorInterface;
use Vankosoft\ApplicationBundle\Component\Status;

use App\Component\Type\PlayerColor;
use App\Component\Utils\Guid;
use App\Form\GameRoomForm;
use App\Entity\TempPlayer;

/**
 *  Multiplayer Game Lobby Controller
 */
class GameRoomsController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory,
        FactoryInterface $tempPlayersFactory
    ) {
        $this->doctrine             = $doctrine;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    public function index( Request $request, PaginatorInterface $paginator ): Response
    {
        $paginatorItems = $this->gamePlayRepository->findBy( [], ['updatedAt' => 'DESC'] );
        $rooms          = $paginator->paginate(
            $paginatorItems,
            $request->query->getInt( 'page', 1 ) /*page number*/,
            10 /*limit per page*/
        );
        
        return $this->render( 'Pages/GameRooms/index.html.twig', [
            'gameRooms' => $rooms,
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
        $room   = $this->gamePlayRepository->find( $id );
        
        $em->remove( $room );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Room Deleted !!!',
        ]);
    }
    
    public function createGameRoomForm( Request $request ): Response
    {
        $form   = $this->createForm( GameRoomForm::class, null, [
            'action' => $this->generateUrl( 'app_handle_game_room_form' ),
            'method' => 'POST'
            
        ]);
        
        return $this->render( 'Pages/GameRooms/Partial/createGameRoom.html.twig', [
            'form' => $form,
        ]);
    }
    
    public function handleGameRoomForm( Request $request ): Response
    {
        $form   = $this->createForm( GameRoomForm::class, null, [
            'action' => $this->generateUrl( 'app_handle_game_room_form' ),
            'method' => 'POST'
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData = $form->getData();
            
            $blackPlayer = $this->CreateTempPlayer();
            
            $game = $this->gamePlayFactory->createNew();
            $game->setGame( $formData['game'] );
            $game->setGuid( Guid::NewGuid() );
            
            $blackPlayer->setGame( $game );
            
            $game->addGamePlayer( $blackPlayer );
            
            $em = $this->doctrine->getManager();
            $em->persist( $game );
            $em->flush();
            
            return $this->redirect( $this->generateUrl( 'app_game_rooms' ) );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_ERROR,
            'message'   => 'Game Room NOT Created !!!',
        ]);
    }
    
    public function joinGameRoom( Request $request ): Response
    {
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Rooms Cleared !!!',
        ]);
    }
    
    public function leaveGameRoom( Request $request ): Response
    {
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Rooms Cleared !!!',
        ]);
    }
    
    public function addUserIntoGameRoom( Request $request ): Response
    {
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Rooms Cleared !!!',
        ]);
    }
    
    private function CreateTempPlayer(): TempPlayer
    {
        $player = $this->getUser()->getPlayer();
        $playerPosition = PlayerColor::Black ;
        
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setPosition( $playerPosition->toString() );
        
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
}