<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MercureSubscriptionsController extends AbstractController
{
    public function index( Request $request ): Response
    {
        $gameSettings = [
            'socketRatchetTestUrl'  => $this->getParameter( 'vs_websocket_ratchet_test_url' ),
            'socketSwooleTestUrl'   => $this->getParameter( 'vs_websocket_swoole_test_url' ),
        ];
        
        return $this->render( 'Pages/MercureSubscriptions/index.html.twig', [
            'gameSettings'  => $gameSettings,
        ]);
    }
}
