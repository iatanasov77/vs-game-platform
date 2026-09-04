<?php namespace App\Component\Manager\Websocket;

use App\Component\Type\PlayerColor;

trait BoardGameConnect
{
    protected function connectBoardGameOwner( $manager, $webSocket, $gamePlayer, $playAi ): void
    {
        $manager->Game->CurrentPlayer = PlayerColor::Black;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerColor::White->value );
    }
    
    protected function connectBoardGameOpponent( $manager, $webSocket, $gamePlayer, $playAi ): void
    {
        $color = $manager->Clients->get( PlayerColor::Black->value ) == null ? PlayerColor::Black : PlayerColor::White;
        $colorName = $color === PlayerColor::Black ? 'Black' : 'White';
        $this->logger->log( "{$colorName} player disconnected.", 'GameService' );
        
        $manager->Game->CurrentPlayer = $color;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerColor::White->value );
    }
}
