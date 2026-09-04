<?php namespace App\Component\Manager\Websocket;

use App\Component\Utils\Guid;
use App\Component\Type\PlayerPosition;
use App\Component\Dto\GameCookieDto;
use App\Component\Manager\CardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\CardGame\Player;
use App\Entity\GamePlayer;

trait SvaraGameConnect
{
    protected function connectSvaraGameOwner(
        CardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GamePlayer $gamePlayer,
        $playAi
    ): void {
        $manager->Game->CurrentPlayer = PlayerPosition::South;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerPosition::East->value );
        $this->SendConnectionLost( $manager, PlayerPosition::North->value );
        $this->SendConnectionLost( $manager, PlayerPosition::West->value );
    }
    
    protected function connectSvaraGameOpponent(
        CardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GamePlayer $gamePlayer,
        $playAi
    ): void {
        $position = $manager->Clients->get( PlayerPosition::South->value ) == null ? PlayerPosition::South : PlayerPosition::East;
        //$colorName = $color === PlayerColor::Black ? 'Black' : 'White';
        //$this->logger->log( "{$colorName} player disconnected.", 'GameService' );
        
        $manager->Game->CurrentPlayer = $position;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerPosition::East->value );
        $this->SendConnectionLost( $manager, PlayerPosition::North->value );
        $this->SendConnectionLost( $manager, PlayerPosition::West->value );
    }
    
    protected function reconnectSvaraGame(
        ?CardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GameCookieDto $cookie,
        $dbUser
    ): ?string {
        $position = $cookie->position;
        if ( $manager && Player::MyPosition( $manager, $dbUser, $position ) ) {
            
            $this->logger->log( "Restoring game {$cookie->id} for {$position->value}", 'GameService' );
            
            // entering socket loop
            $manager->Restore( $position->value, $webSocket );
            
            return $cookie->id;
        }
        
        return null;
    }
    
    protected function isSvaraGameAlreadyStarted( CardGameManager $manager, $userId ): bool
    {
        // Guest vs guest must be allowed. When guest games are enabled.
        if (
            (
                $manager->Game->Players[PlayerPosition::South->value]->Id == $userId ||
                $manager->Game->Players[PlayerPosition::West->value]->Id == $userId ||
                $manager->Game->Players[PlayerPosition::North->value]->Id == $userId ||
                $manager->Game->Players[PlayerPosition::East->value]->Id == $userId
            ) &&
            $userId != Guid::Empty()
        ) {
            $this->logger->log( "Game Already Started", 'GameService' );
            return true;
        }
        
        return false;
    }
}
