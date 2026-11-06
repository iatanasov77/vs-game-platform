<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Vankosoft\ApplicationBundle\Component\Status;

use App\Component\Dto\Actions\MercureActionNames;
use App\Component\Dto\Actions\MercureActionDto;
use App\Component\Manager\MercureManager;

class TestMercurePublishController
{
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var MercureManager */
    private $manager;
    
    public function __construct( SerializerInterface $serializer, MercureManager $manager )
    {
        $this->serializer   = $serializer;
        $this->manager      = $manager;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $postData   = \json_decode( $request->getContent(), true );
        $topic      = $postData['topic'];
        $message    = $postData['message'];
        
        $response   = $this->manager->DoAction( $topic, $message );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => $response,
        ];
        return new JsonResponse( $response );
    }
    
    public function sendTestMessageAction( Request $request ): JsonResponse
    {
        $postData = \json_decode( $request->getContent(), true );
        $this->mercurePublisher->publishData( $postData['topic'], ['status' => 'checked out'] );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Response: Published From Backend',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function createGameLobby( $gameType, Request $request ): JsonResponse
    {
        if ( $gameType == GamePlatform::GAME_TYPE_CARD_GAME ) {
            $loby = $this->_createCardGameLobby();
        } else {
            $loby = $this->_createBoardGameLobby();
        }
        
        $postData = \json_decode( $request->getContent(), true );
        $gamePlay = $this->createResponseBody( $loby );
        $this->mercurePublisher->publishData( $postData['topic'], $gamePlay );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Created',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function joinGameLobby( $roomId, Request $request ): JsonResponse
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->vsSecurityBridge->getUser()->getPlayer();
        
        $this->logger->log( "Join Game Lobby !!!" );
        $tempPlayer = $this->createTempPlayer( $room->getGame(), $player );
        $tempPlayer->setGame( $room );
        $room->addGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Joined',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function exitGameLobby( $roomId, Request $request ): JsonResponse
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->vsSecurityBridge->getUser()->getPlayer();
        
        $tempPlayer = $room->getTempPlayer( $player );
        $room->removeGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Leaved',
        ];
        
        return new JsonResponse( $response );
    }
}
