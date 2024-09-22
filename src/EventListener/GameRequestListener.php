<?php namespace App\EventListener;

use function Amp\async;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\Request;
use App\Component\GamePlatform;
use App\Component\GameService;
use App\Component\Websocket\WebsocketClient;

final class GameRequestListener
{
    /** @var WebsocketClient */
    private $wsClient;
    
    /** @var GameService */
    private $gameService;
    
    public function __construct( WebsocketClient $wsClient, GameService $gameService )
    {
        $this->wsClient     = $wsClient;
        $this->gameService  = $gameService;
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
                $this->connectGame( $request, 'backgammon', GamePlatform::BACKGAMMON_GAME_COOKIE_KEY );
                break;
        }
    }
    
    private function connectGame( Request $request, $gameCode, $cookieKey )
    {
        $gameCookie = $request->cookies->get( $cookieKey );
        $userId     = $request->query->get( 'userId' );
        $gameId     = $request->query->get( 'gameId' );
        $playAi     = $request->query->get( 'playAi', true );
        $forGold    = $request->query->get( 'forGold', true );
        
        $this->gameService->Connect( $this->wsClient, $gameCode, $userId, $gameId, $playAi, $forGold, $gameCookie );
    }
}
