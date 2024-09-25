<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\Websocket\WebsocketClientFactory;

class TestWebSocketController extends AbstractController
{
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    public function __construct( WebsocketClientFactory $wsClientFactory )
    {
        $this->wsClientFactory  = $wsClientFactory;
    }
    
    public function client1( Request $request ): Response
    {
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $clientSettings           = [
            'socketServiceUrl'      => $this->getParameter( 'app_websocket_url' ),
            'apiVerifySiganature'   => $signature,
        ];
        
        return $this->render( 'Pages/WebSocketTest/client_1.html.twig', [
            'clientSettings'    => $clientSettings,
        ]);
    }
    
    public function client2( Request $request ): Response
    {
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $clientSettings           = [
            'socketServiceUrl'      => $this->getParameter( 'app_websocket_url' ),
            'apiVerifySiganature'   => $signature,
        ];
        
        $client1    = $this->wsClientFactory->createNew();
        $client2    = $this->wsClientFactory->createNew();
        
        return $this->render( 'Pages/WebSocketTest/client_2.html.twig', [
            'clientSettings'    => $clientSettings,
            
            'client1'   => $client1,
            'client2'   => $client2,
        ]);
    }
    
    public function publish( Request $request ): Response
    {
        $data       = \json_decode( $request->getContent(), true );
        $testObject = new \stdClass();
        
        $testObject->test       = 'chat';
        $testObject->user       = $data['user'];
        $testObject->message    = $data['message'];
        
        $context = new \ZMQContext();
        $socket = $context->getSocket( \ZMQ::SOCKET_PUSH, 'my pusher' );
        $socket->connect( "tcp://localhost:5555" );
        
        $socket->send( \json_encode( $testObject ) );
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
        ]);
    }
}
