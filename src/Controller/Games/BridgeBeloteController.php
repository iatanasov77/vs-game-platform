<?php namespace App\Controller\Games;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class BridgeBeloteController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'bridge-belote';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => $gameSlug,
            'socketChatUrl'         => $this->getParameter( 'app_websocket_chat_url' ),
            'socketGameUrl'         => $this->getParameter( 'app_websocket_game_url' ),
            'apiVerifySiganature'   => $signature,
            'timeoutBetweenPlayers' => $gamePlatformSettings->getTimeoutBetweenPlayers(),
            
            'queryParams'           => [
                'gameId'    => $request->query->get( 'gameId' ),
                'inviteId'  => $request->query->get( 'inviteId' ),
            ],
        ];
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/bridge-belote.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
