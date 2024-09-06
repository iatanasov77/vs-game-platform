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
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : null;
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'apiVerifySiganature'   => $signature,
            'timeoutBetweenPlayers' => $gamePlatformSettings->getTimeoutBetweenPlayers(),
        ];
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Backgammon/index.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
