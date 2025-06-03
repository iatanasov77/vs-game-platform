<?php namespace App\Controller\Games;

use App\Controller\Application\GameController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\Utils\Keys;

class BackgammonController extends GameController
{
    public function normal( Request $request ): Response
    {
        $game       = $this->gamesRepository->findOneBy( ['slug' => Keys::BACKGAMMON_NORMAL_KEY] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => Keys::BACKGAMMON_NORMAL_KEY,
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
            $this->templatingEngine->render( $this->getTemplate( Keys::BACKGAMMON_NORMAL_KEY, 'Pages/Games/Backgammon/normal.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
    
    public function gulbara( Request $request ): Response
    {
        $game       = $this->gamesRepository->findOneBy( ['slug' => Keys::BACKGAMMON_GULBARA_KEY] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => Keys::BACKGAMMON_GULBARA_KEY,
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
            $this->templatingEngine->render( $this->getTemplate( Keys::BACKGAMMON_GULBARA_KEY, 'Pages/Games/Backgammon/gulbara.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
    
    public function tapa( Request $request ): Response
    {
        $game       = $this->gamesRepository->findOneBy( ['slug' => Keys::BACKGAMMON_TAPA_KEY] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => Keys::BACKGAMMON_TAPA_KEY,
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
            $this->templatingEngine->render( $this->getTemplate( Keys::BACKGAMMON_TAPA_KEY, 'Pages/Games/Backgammon/tapa.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
