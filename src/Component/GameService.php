<?php namespace App\Component;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use function Amp\async;

use App\Component\Manager\GameManager;
use App\Component\System\Guid;
use App\Component\Dto\GameCookieDto;
use App\Component\Dto\PlayerColor;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Type\GameState;
use App\Component\Websocket\WebsocketClient;
use App\Component\Websocket\WebSocketState;

final class GameService
{
    /** @var LoggerInterface */
    private $logger;
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var GameManager */
    private $gameManager;
    
    /** @var Collection | GameManager[] */
    private $AllGames;
    
    public function __construct(
        LoggerInterface $logger,
        RepositoryInterface $usersRepository,
        GameManager $gameManager
    ) {
        $this->logger           = $logger;
        $this->usersRepository  = $usersRepository;
        $this->gameManager      = $gameManager;
        
        $this->AllGames         = new ArrayCollection();
    }
    
    public function Connect( WebSocketClient $webSocket, $gameCode, $userId, $gameId, $playAi, $forGold, ?string $gameCookie ): void
    {
        $dbUser = $this->GetDbUser( $userId );
        
        if ( $this->TryReConnect( $webSocket, $gameCookie, $dbUser ) ) {
            // Game disconnected here
            return;
        }
        
        if ( ! empty ( $gameId ) ) {
            async( \Closure::fromCallable( [$this, 'ConnectInvite'] ), [$webSocket, $dbUser, $gameId] )->await();
            
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
        }
        
        $manager = \array_shift( $managers );
        if ( $manager == null || $playAi ) {
            //$manager = new GameManager( $this->logger, $forGold );
            $manager = $this->gameManager;
            
            //$manager.Ended += Game_Ended;
            $manager->SearchingOpponent = ! $playAi;
            $manager->GameCode          = $gameCode;
            
            /*
            $this->AllGames[]   = $manager;
            $this->logger->info( "Added a new game and waiting for opponent. Game id {$manager->Game->Id}" );
            
            // entering socket loop
            async( \Closure::fromCallable( [$manager, 'ConnectAndListen'] ), [$webSocket, PlayerColor::black, $dbUser, $playAi] )->await();
            async( \Closure::fromCallable( [$this, 'SendConnectionLost'] ), [PlayerColor::white, $manager] )->await();
            //This is the end of the connection
            */
        } else {
            $manager->SearchingOpponent = false;
            $manager->GameCode          = $gameCode;
            
            $this->logger->info( "Found a game and added a second player. Game id {$manager->Game->Id}" );
            $color = $manager->Client1 == null ? PlayerColor::Black : PlayerColor::White;
            
            // entering socket loop
            async( \Closure::fromCallable( [$manager, 'ConnectAndListen'] ), [$webSocket, $color, $dbUser, false] )->await();
            $this->logger->info( "{$color} player disconnected.");
            async( \Closure::fromCallable( [$this, 'SendConnectionLost'] ), [PlayerColor::black, $manager] )->await();
            //This is the end of the connection
        }
        
        $this->RemoveDissconnected( $manager );
    }
    
    private static function GameAlreadyStarted( Collection $managers, $userId ): bool
    {
        foreach ( $managers as $m ) {
            // Guest vs guest must be allowed. When guest games are enabled.
            if (
                $m->Game->BlackPlayer->Id == $userId ||
                $m->Game>WhitePlayer->Id == $userId &&
                $userId != Guid::Empty
            ) {
                return true;
            }
        }
        
        return false;
    }
    
    private function GetDbUser( $userId ): ?UserInterface
    {
        return $userId ? $this->usersRepository->find( $userId ) : null;
    }
    
    private function TryReConnect( WebSocketClient $webSocket, ?string $gameCookie, ?UserInterface $dbUser ): bool
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
                    $gameManager->Engine = new Ai.Engine( gameManager.Game );
                    $this->logger->info( "Restoring game {$cookie->id} for {$color}" );
                    
                    // entering socket loop
                    async( \Closure::fromCallable( [$gameManager, 'Restore'] ), [$color, $webSocket] )->await();
                    
                    $otherColor = $color == PlayerColor::black ? PlayerColor::white : PlayerColor::black;
                    async( \Closure::fromCallable( [$this, 'SendConnectionLost'] ), [$otherColor, $gameManager] )->await();
                    
                    // socket loop exited
                    $this->RemoveDissconnected( $gameManager );
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    private function RemoveDissconnected( GameManager $manager ): void
    {
        if (
            ( $manager->Client1 == null || $manager->Client1->State != WebSocketState::Open ) &&
            ( $manager->Client2 == null || $manager->Client2->State != WebSocketState::Open )
        ) {
            $this->AllGames->removeElement( $manager );
            //$this->logger->info( "Removing game {$manager->Game->Id} which is not used." );
        }
    }
    
    private static function ConnectInvite( WebSocketClient $webSocket, UserInterface $dbUser, string $gameInviteId ): void
    {
        $manager = $this->AllGames->filter(
            function( $entry ) use ( $cookie ) {
                return $entry->Game->Id == $gameInviteId && ( $entry->Client1 == null || $entry->Client2 == null );
            }
        )->first();
        
        if ( $manager == null ) {
            //async( \Closure::fromCallable( [$webSocket, 'close'] ), [WebSocketCloseStatus.InvalidPayloadData, "Invite link has expired", CancellationToken.None] )->await();
            
            return;
        }
        
        $color = PlayerColor::Black;
        if ( $manager->Client1 != null )
            $color = PlayerColor::White;
            
        async( \Closure::fromCallable( [$manager, 'ConnectAndListen'] ), [$webSocket, $color, $dbUser, false] )->await();
        
        self::RemoveDissconnected( $manager );
        async( \Closure::fromCallable( [$this, 'SendConnectionLost'] ), [PlayerColor::white, $manager] )->await();
    }
    
    private static function SendConnectionLost( PlayerColor $color, GameManager $manager )
    {
        $socket = $manager->Client1;
        if ( $color == PlayerColor::white )
            $socket = $manager->Client2;
        
        if ( $socket != null && $socket.State == WebSocketState::Open ) {
            $action     = new ConnectionInfoActionDto();
            $connection = new ConnectionDto();
            $connection->connected = false;
            $action->connection = $connection;
            
            async( \Closure::fromCallable( [$manager, 'Send'] ), [$socket, $action] )->await();
        }
    }
    
    private static function MyColor( GameManager $manager, UserInterface $dbUser, PlayerColor $color ): bool
    {
        //prevents someone with same game id, get someone elses side in the game.
        $player = $manager->Game->BlackPlayer;
        if ( $color == PlayerColor::white )
            $player = $manager->Game->WhitePlayer;
            
        return $dbUser != null && $dbUser->getId() == $player->Id;
    }
    
    private static function Game_Ended( object $sender ): void
    {
        $this->AllGames->removeElement( $sender );
    }
    
    private function orderAllGames(): Collection
    {
        $gamesIterator  = $this->AllGames->getIterator();
        $gamesIterator->uasort( function ( $a, $b ) {
            return $a->Created > $b->Created;
        });
            
        return new ArrayCollection( \iterator_to_array( $gamesIterator ) );
    }
}