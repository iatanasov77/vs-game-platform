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

use App\Component\GamePlatform;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;
use App\Component\Utils\Guid;
use App\Form\GameRoomForm;
use App\Form\GameRoomPlayerForm;
use App\Entity\Game;
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
            'gameRooms'     => $rooms,
            'gameRoomTeams' => $this->createTeams( $rooms ),
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
            $baseGame = $formData['game'];
            
            $player = $this->getUser()->getPlayer();
            $tempPlayer = $this->createTempPlayer( $baseGame, $player );
            
            $game = $this->gamePlayFactory->createNew();
            $game->setGame( $baseGame );
            $game->setGuid( Guid::NewGuid() );
            
            $tempPlayer->setGame( $game );
            
            $game->addGamePlayer( $tempPlayer );
            
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
    
    public function joinGameRoom( $roomId, Request $request ): Response
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->getUser()->getPlayer();
        
        $tempPlayer = $this->createTempPlayer( $room->getGame(), $player );
        $room->addGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Room Joined !!!',
        ]);
    }
    
    public function leaveGameRoom( $roomId, Request $request ): Response
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->getUser()->getPlayer();
        
        $tempPlayer = $room->getTempPlayer( $player );
        $room->removeGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'message'   => 'Game Room Leaved !!!',
        ]);
    }
    
    public function addUserIntoGameRoomForm( $roomId, Request $request ): Response
    {
        $room       = $this->gamePlayRepository->find( $roomId );
        $gameType   = $room->getGame()->getType();
        
        $form   = $this->createForm( GameRoomPlayerForm::class, null, [
            'action'    => $this->generateUrl( 'app_handle_add_user_into_game_room_form', ['roomId' => $roomId] ),
            'method'    => 'POST',
            'gameType'  => $gameType,
        ]);
        
        return $this->render( 'Pages/GameRooms/Partial/addGameRoomPlayer.html.twig', [
            'form'      => $form,
            'gameType'  => $gameType,
        ]);
    }
    
    public function handleUserIntoGameRoomForm( $roomId, Request $request ): Response
    {
        $room       = $this->gamePlayRepository->find( $roomId );
        $gameType   = $room->getGame()->getType();
        
        $form   = $this->createForm( GameRoomPlayerForm::class, null, [
            'action'    => $this->generateUrl( 'app_handle_add_user_into_game_room_form', ['roomId' => $roomId] ),
            'method'    => 'POST',
            'gameType'  => $gameType,
        ]);
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $formData = $form->getData();
            $player = $formData['player'];
            
            $tempPlayer = $this->createTempPlayer( $room->getGame(), $player );
            $tempPlayer->setGame( $room );
            
            $room->addGamePlayer( $tempPlayer );
            
            $em = $this->doctrine->getManager();
            $em->persist( $room );
            $em->flush();
            
            return $this->redirect( $this->generateUrl( 'app_game_rooms' ) );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_ERROR,
            'message'   => 'User NOT Added Into Game Room  !!!',
        ]);
    }
    
    private function createTempPlayer( Game $baseGame, $player ): TempPlayer
    {
        $tempPlayer = $this->tempPlayersFactory->createNew();
        
        if ( $baseGame == GamePlatform::GAME_TYPE_BOARD_GAME ) {
            $tempPlayer->setColor( PlayerColor::Black->toString() );
        } else {
            $tempPlayer->setPosition( PlayerPosition::South->toString() );
        }
        
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
    
    private function createTeams( $rooms ): array
    {
        $teams = [];
        foreach ( $rooms as $room ) {
            foreach ( $room->getGamePlayers() as $player ) {
                if ( $player->getColor() ) {
                    $teams[$room->getId()][$player->getColor()][] = $player;
                }
                
                if ( $player->getPosition() ) {
                    if (
                        $player->getPosition() == PlayerPosition::North->toString() ||
                        $player->getPosition() == PlayerPosition::South->toString()
                    ) {
                        $teams[$room->getId()][CardGameTeam::SouthNorth->toString()][] = $player;
                    } else {
                        $teams[$room->getId()][CardGameTeam::EastWest->toString()][] = $player;
                    }
                }
            }
        }
        
        return $teams;
    }
}