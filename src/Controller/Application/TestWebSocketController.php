<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Component\Websocket\WebsocketClientFactory;

class TestWebSocketController extends AbstractController
{
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    public function __construct( WebsocketClientFactory $wsClientFactory )
    {
        $this->wsClientFactory  = $wsClientFactory;
    }
    
    public function index( Request $request ): Response
    {
        return $this->render( 'Pages/WebSocketTest/index.html.twig', [
            
        ]);
    }
    
    public function publish( Request $request ): Response
    {
        $client1    = $this->wsClientFactory->createNew();
        $client2    = $this->wsClientFactory->createNew();
        
        return $this->render( 'Pages/WebSocketTest/publish.html.twig', [
            'client1'   => $client1,
            'client2'   => $client2,
        ]);
    }
}
