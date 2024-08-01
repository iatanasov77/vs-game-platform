<?php namespace App\Controller\Chess;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'chess';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : null;
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Chess/index.html.twig' ), [
                'game'                  => $game,
                'apiVerifySiganature'   => $signature,
            ])
        );
    }
}
