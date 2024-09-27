<?php namespace App\Component;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;

use App\Component\Ai\Backgammon\Engine as AiEngine;
use App\Component\Manager\GameManager;
use App\Component\System\Guid;
use App\Component\Dto\GameCookieDto;
use App\Component\Dto\ConnectionDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Websocket\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

class ZmqGameService extends GameService
{
    public function Connect( WebsocketClientInterface $webSocket, $gameCode, $userId, $gameId, $playAi, $forGold, ?string $gameCookie ): void
    {
        $dbUser = $this->GetDbUser( $userId );
        if ( ! $dbUser ) {
            return;
        }
        
        if ( $this->TryReConnect( $webSocket, $gameCookie, $dbUser ) ) {
            // Game disconnected here
            return;
        }
        
        if ( ! empty ( $gameId ) ) {
            $this->ConnectInvite( $webSocket, $dbUser, $gameId );
            // Game disconnected here.
            return;
        }
        
        //todo: pair with someone equal ranking?
        
        // Search any game, oldest first.
        $managers = $this->orderAllGames()->filter(
            function( $entry ) {
                return ( $entry->Client2 == null || $entry->Client1 == null ) && $entry->SearchingOpponent;
            }
        );
        
        if ( self::GameAlreadyStarted( $managers, $userId ) ) {
            $warning = "The user {$userId} has already started a game";
            $this->logger->warning( $warning );
            throw new \Exception( $warning );
        }
        
        $isGuest = ! $dbUser; // $dbUser->getId() == Guid::Empty();
        // filter out games having a logged in player
        if ( $isGuest ) {
            $managers = $managers->filter(
                function( $entry ) {
                    return $entry->Game->BlackPlayer->Id != Guid::Empty() || $entry->Game->WhitePlayer->Id != Guid::Empty();
                }
            )->toArray();
        } else {
            $managers = $managers->toArray();
        }
        
        $manager = \array_shift( $managers );
        if ( $manager == null || $playAi ) {
            //$manager = new GameManager( $this->logger, $forGold );
            $manager = $this->gameManager;
            
            //$manager.Ended += Game_Ended;
            $manager->SearchingOpponent = ! $playAi;
            $manager->GameCode          = $gameCode;
            
            $this->AllGames[]   = $manager;
            
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, PlayerColor::Black, $dbUser, $playAi );
            $this->SendConnectionLost( PlayerColor::White, $manager );
            
            if ( $manager->Game ) {
                $this->logger->info( "Added a new game and waiting for opponent. Game id {$manager->Game->Id}" );
            }
            //This is the end of the connection
        } else {
            $manager->SearchingOpponent = false;
            $manager->GameCode          = $gameCode;
            
            $this->logger->info( "Found a game and added a second player. Game id {$manager->Game->Id}" );
            $color = $manager->Client1 == null ? PlayerColor::Black : PlayerColor::White;
            
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, $color, $dbUser, false );
            $this->logger->info( "{$color} player disconnected." );
            $this->SendConnectionLost( PlayerColor::Black, $manager );
            //This is the end of the connection
        }
        
        $this->RemoveDissconnected( $manager );
    }
    
    protected function TryReConnect( WebsocketClientInterface $webSocket, ?string $gameCookie, ?UserInterface $dbUser ): bool
    {
        // Find existing game to reconnect to.
        if ( $gameCookie ) {
            $cookie = GameCookieDto::TryParse( $gameCookie );
            $color = $cookie->color;
            
            if ( $cookie != null )
            {
                $gameManager = $this->AllGames->filter(
                    function( $entry ) use ( $cookie ) {
                        return $entry->Game->Id == $cookie->id && $entry->Game->PlayState == GameState::Ended;
                    }
                )->first();
                
                if ( $gameManager != null && self::MyColor( $gameManager, $dbUser, $color ) )
                {
                    $gameManager->Engine = new AiEngine( $this->gameManager->Game );
                    $this->logger->info( "Restoring game {$cookie->id} for {$color}" );
                    
                    // entering socket loop
                    async( \Closure::fromCallable( [$gameManager, 'Restore'] ), [$color, $webSocket] )->await();
                    
                    $otherColor = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                    async( \Closure::fromCallable( [$this, 'SendConnectionLost'] ), [$otherColor, $gameManager] )->await();
                    
                    // socket loop exited
                    $this->RemoveDissconnected( $gameManager );
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    
    protected static function SendConnectionLost( PlayerColor $color, GameManager $manager )
    {
        $socket = $manager->Client1;
        if ( $color == PlayerColor::White ) {
            $socket = $manager->Client2;
        }
            
        if ( $socket != null && $socket->State == WebSocketState::Open ) {
            $action     = new ConnectionInfoActionDto();
            $connection = new ConnectionDto();
            $connection->connected = false;
            $action->connection = $connection;
            
            $manager->Send( $socket, $action );
        }
    }
}
