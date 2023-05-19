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
        
        $signature  = null;
        if ( $this->getUser() ) {
            $signature  = $this->apiManager->getVerifySignature( $this->getUser(), 'vs_api_login_by_signature' );
        }
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Chess/index.html.twig' ), [
                'game'                  => $game,
                'urlLoginBySignature'   => $signature ? $signature->getSignedUrl() : null,
            ])
        );
    }
}
