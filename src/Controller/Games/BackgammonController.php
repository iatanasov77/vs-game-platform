<?php namespace App\Controller\Games;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\Utils\Keys;

class BackgammonController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'backgammon';
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
                'playAi'    => $request->query->get( 'playAi' ),
                'forGold'   => $request->query->get( 'forGold' ),
                'tutorial'  => $request->query->get( 'tutorial' ),
                'editing'   => $request->query->get( 'editing' ),
                'inviteId'  => $request->query->get( 'inviteId' ),
            ],
        ];
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( Keys::BACKGAMMON_NORMAL_KEY, 'Pages/Games/backgammon.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
