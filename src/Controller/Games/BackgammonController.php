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
        $gameSlug   = 'backgammon';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => $gameSlug,
            'socketPublisherUrl'    => $this->getParameter( 'app_websocket_publisher_url' ),
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
        
        
//         return new JsonResponse([
//             'status'        => Status::STATUS_OK,
//             'gameCookie'    => $request->cookies->get( Keys::GAME_ID_KEY ),
//         ]);
        
        return new Response(
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/Backgammon/normal.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
    
    public function gulbara( Request $request ): Response
    {
        $gameSlug   = 'backgammon';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => $gameSlug,
            'socketPublisherUrl'    => $this->getParameter( 'app_websocket_publisher_url' ),
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
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/Backgammon/gulbara.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
    
    public function tapa( Request $request ): Response
    {
        $gameSlug   = 'backgammon';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $signature  = $this->getUser() ? $this->getUser()->getApiVerifySiganature() : '';
        
        $gamePlatformSettings   = $this->applicationContext->getApplication()->getGamePlatformApplication()->getSettings();
        $gameSettings           = [
            'gameSlug'              => $gameSlug,
            'socketPublisherUrl'    => $this->getParameter( 'app_websocket_publisher_url' ),
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
            $this->templatingEngine->render( $this->getTemplate( $gameSlug , 'Pages/Games/Backgammon/tapa.html.twig' ), [
                'game'          => $game,
                'gameSettings'  => $gameSettings,
            ])
        );
    }
}
