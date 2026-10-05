<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugController extends AbstractController
{
    public function debugWebsocket( Request $request ): Response
    {
        $gameSettings = [
            'socketSwooleTestUrl' => $this->getParameter( 'app_websocket_swoole_test_url' ),
        ];
        
        return $this->render( 'Pages/GamesDebug/debug-websocket.html.twig', [
            'gameSettings'  => $gameSettings,
        ]);
    }
    
    public function debugSse( Request $request ): Response
    {
        return $this->render( 'Pages/GamesDebug/debug-sse.html.twig', [
            
        ]);
    }
}
