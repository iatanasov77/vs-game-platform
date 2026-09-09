<?php namespace App\Component\Manager\Websocket;

use App\Component\Utils\Guid;
use App\Component\Type\PlayerColor;
use App\Component\Dto\GameCookieDto;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Component\Manager\BoardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\BoardGame\Player;
use App\Entity\GamePlayer;

trait BoardGameConnect
{
    protected function connectBoardGameOwner(
        BoardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GamePlayer $gamePlayer,
        $playAi
    ): void {
        $manager->Game->CurrentPlayer = PlayerColor::Black;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerColor::White->value );
    }
    
    protected function connectBoardGameOpponent(
        BoardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GamePlayer $gamePlayer,
        $playAi
    ): void {
        $color = $manager->Clients->get( PlayerColor::Black->value ) == null ? PlayerColor::Black : PlayerColor::White;
        $colorName = $color === PlayerColor::Black ? 'Black' : 'White';
        $this->logger->log( "{$colorName} player disconnected.", 'GameService' );
        
        $manager->Game->CurrentPlayer = $color;
        $manager->ConnectAndListen( $webSocket, $gamePlayer, $playAi );
        $this->SendConnectionLost( $manager, PlayerColor::White->value );
    }
    
    protected function reconnectBoardGame(
        ?BoardGameManager $manager,
        WebsocketClientInterface $webSocket,
        GameCookieDto $cookie,
        $dbUser
    ): ?string {
        $color = $cookie->color;
        if ( $manager && Player::MyColor( $manager, $dbUser, $color ) ) {
            $manager->Engine = AiEngineFactory::CreateAiEngine(
                $manager->GameCode,
                $manager->GameVariant,
                $this->logger,
                $manager->Game
            );
            $this->logger->log( "Restoring game {$cookie->id} for {$color->value}", 'GameService' );
            
            // entering socket loop
            $manager->Restore( $color->value, $webSocket );
            
            $otherColor = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
            $this->SendConnectionLost( $manager, $otherColor->value );
            
            // socket loop exited
            $this->RemoveDissconnected( $manager );
            
            return $cookie->id;
        }
        
        return null;
    }
    
    protected function isBoardGameAlreadyStarted( BoardGameManager $manager, $userId ): bool
    {
        // Guest vs guest must be allowed. When guest games are enabled.
        if (
            $manager->Game->BlackPlayer->Id == $userId ||
            $manager->Game->WhitePlayer->Id == $userId &&
            $userId != Guid::Empty()
        ) {
            $this->logger->log( "Game Already Started", 'GameService' );
            return true;
        }
        
        return false;
    }
}
