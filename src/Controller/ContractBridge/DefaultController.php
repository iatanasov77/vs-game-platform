<?php namespace App\Controller\ContractBridge;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'contract-bridge';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getVerifySignature();
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/ContractBridge/index.html.twig' ), [
                'game'                  => $game,
                'urlLoginBySignature'   => $signature ? $signature->getSignedUrl() : null,
            ])
        );
    }
}
