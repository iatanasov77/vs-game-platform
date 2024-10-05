<?php namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Component\GamePlatform;
use App\Component\GameService;
use App\Component\ZmqGameService;
use App\Component\Websocket\WebsocketClientFactory;

final class GameRequestListener
{
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    /** @var GameService */
    private $gameWebsocketService;
    
    /** @var ZmqGameService */
    private $gameZmqService;
    
    public function __construct(
        WebsocketClientFactory $wsClientFactory,
//         GameService $gameWebsocketService,
        ZmqGameService $gameZmqService
    ) {
        $this->wsClientFactory      = $wsClientFactory;
//         $this->gameWebsocketService = $gameWebsocketService;
        $this->gameZmqService       = $gameZmqService;
    }
    
    public function onKernelRequest( RequestEvent $event ): void
    {
        if ( ! $event->isMainRequest() ) {
            // don't do anything if it's not the main request
            return;
        }
        $request    = $event->getRequest();
        $routeName = $request->get( '_route' );
        
        switch ( $routeName ) {
            case 'backgammon':
                //$this->connectGameWithWebsocket( $request, 'backgammon', GamePlatform::BACKGAMMON_GAME_COOKIE_KEY );
                $this->connectGameWithZmq( $request, 'backgammon', GamePlatform::BACKGAMMON_GAME_COOKIE_KEY );
                break;
        }
    }
    
    private function connectGameWithWebsocket( Request $request, $gameCode, $cookieKey )
    {
        $gameCookie = $request->cookies->get( $cookieKey );
        $userId     = $request->query->get( 'userId' );
        $gameId     = $request->query->get( 'gameId' );
        $playAi     = $request->query->get( 'playAi', true );
        $forGold    = $request->query->get( 'forGold', true );
        
        $this->gameWebsocketService->Connect( $this->wsClientFactory->createServerClient(), $gameCode, $userId, $gameId, $playAi, $forGold, $gameCookie );
    }
    
    private function connectGameWithZmq( Request $request, $gameCode, $cookieKey )
    {
        $gameCookie = $request->cookies->get( $cookieKey );
        $userId     = $request->query->get( 'userId' );
        $gameId     = $request->query->get( 'gameId' );
        $playAi     = $request->query->get( 'playAi', true );
        $forGold    = $request->query->get( 'forGold', true );
        
        $this->gameZmqService->Connect( $this->wsClientFactory->createZmqClient(), $gameCode, $userId, $gameId, $playAi, $forGold, $gameCookie );
    }
}
