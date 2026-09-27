<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DebugController extends AbstractController
{
    public function debugSse( Request $request ): Response
    {
        return $this->render( 'Pages/GamesDebug/debug-sse.html.twig', [
            
        ]);
    }
}
