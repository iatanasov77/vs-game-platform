<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugController extends AbstractController
{
    public function debugWebsocket( Request $request ): Response
    {
        $gameSettings = [
            'socketRatchetTestUrl'  => $this->getParameter( 'vs_websocket_ratchet_test_url' ),
            'socketSwooleTestUrl'   => $this->getParameter( 'vs_websocket_swoole_test_url' ),
        ];
        
        return $this->render( 'Pages/GamesDebug/debug-websocket.html.twig', [
            'gameSettings'  => $gameSettings,
        ]);
    }
    
    public function debugSse( Request $request ): Response
    {
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        $gameSettings           = [
            'apiVerifySiganature' => $signature,
        ];
        
        return $this->render( 'Pages/GamesDebug/debug-sse.html.twig', [
            'gameSettings'  => $gameSettings,
        ]);
    }
}
