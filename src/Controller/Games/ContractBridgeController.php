<?php namespace App\Controller\Games;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ContractBridgeController extends GameController
{
    public function index( Request $request ): Response
    {
        $gameSlug   = 'contract-bridge';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => $gameSlug,
            'socketChatUrl'         => $this->getParameter( 'app_websocket_chat_url' ),
            'socketGameUrl'         => $this->getParameter( 'app_websocket_game_url' ),
            'apiVerifySiganature'   => $signature,
            
            'timeoutBetweenPlayers'     => $gamePlatformSettings->getTimeoutBetweenPlayers(),
            'debugGameSounds'           => $gamePlatformSettings->getDebugGameSounds(),
            'debugCardGamePlayerAreas'  => $gamePlatformSettings->getDebugCardGamePlayerAreas(),
            'debugCardGamePlayerCards'  => $gamePlatformSettings->getDebugCardGamePlayerCards(),
            
            'queryParams'           => [
                'gameId'    => $request->query->get( 'gameId' ),
                'inviteId'  => $request->query->get( 'inviteId' ),
            ],
        ];
        
        if ( $game->getStatus() && $game->getStatus() != GamePlatform::GAME_STATUS_DONE ) {
            $this->showGameStatus( $request, $game );
        }
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/contract-bridge.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
