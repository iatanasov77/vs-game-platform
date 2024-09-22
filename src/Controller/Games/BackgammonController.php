<?php namespace App\Controller\Games;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BackgammonController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'backgammon';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'socketServiceUrl'      => $this->getParameter( 'app_websocket_url' ),
            'apiVerifySiganature'   => $signature,
            'timeoutBetweenPlayers' => $gamePlatformSettings->getTimeoutBetweenPlayers(),
        ];
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/backgammon.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
