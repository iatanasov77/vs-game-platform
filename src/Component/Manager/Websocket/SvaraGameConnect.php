<?php namespace App\Component\Manager\Websocket;

use App\Component\Type\PlayerPosition;

trait SvaraGameConnect
{
    protected function connectSvaraGameOwner( $manager, $webSocket, $gamePlayer, $playAi ): void
    {
        $manager->Game->CurrentPlayer = PlayerPosition::South;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerPosition::East->value );
        $this->SendConnectionLost( $manager, PlayerPosition::North->value );
        $this->SendConnectionLost( $manager, PlayerPosition::West->value );
    }
    
    protected function connectSvaraGameOpponent( $manager, $webSocket, $gamePlayer, $playAi ): void
    {
        $position = $manager->Clients->get( PlayerPosition::South->value ) == null ? PlayerPosition::South : PlayerPosition::East;
        //$colorName = $color === PlayerColor::Black ? 'Black' : 'White';
        //$this->logger->log( "{$colorName} player disconnected.", 'GameService' );
        
        $manager->Game->CurrentPlayer = $position;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerPosition::East->value );
        $this->SendConnectionLost( $manager, PlayerPosition::North->value );
        $this->SendConnectionLost( $manager, PlayerPosition::West->value );
    }
}
