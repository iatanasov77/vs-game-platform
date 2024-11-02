<?php namespace App\Component;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;

use App\Component\Manager\GameManagerInterface;
use App\Component\Manager\GameManagerFactory;
use App\Component\Ai\Backgammon\Engine as AiEngine;
use App\Component\System\Guid;

// DTO Objects
use App\Component\Dto\TestWebsocketDto;
use App\Component\Dto\GameCookieDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\ConnectionDto;

use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

class GameService
{
    /** @var LoggerInterface */
    protected $logger;
    
    /** @var SerializerInterface */
    protected $serializer;
    
    /** @var HttpClientInterface */
    protected $httpClient;
    
    /** @var RepositoryInterface */
    protected $usersRepository;
    
    /** @var SecurityBridge */
    protected $securityBridge;
    
    /** @var GameManagerFactory */
    protected $managerFactory;
    
    /** @var Collection | GameManagerInterface[] */
    protected $AllGames;
    
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        HttpClientInterface $httpClient,
        RepositoryInterface $usersRepository,
        SecurityBridge $securityBridge,
        GameManagerFactory $managerFactory
    ) {
        $this->logger           = $logger;
        $this->serializer       = $serializer;
        $this->httpClient       = $httpClient;
        $this->usersRepository  = $usersRepository;
        $this->securityBridge   = $securityBridge;
        $this->managerFactory   = $managerFactory;
        
        $this->AllGames         = new ArrayCollection();
    }
    
    public function getGameManager( string $gameId ): ?GameManagerInterface
    {
        return isset( $this->AllGames[$gameId] ) ? $this->AllGames[$gameId] : null;
    }
    
    public function Connect( WebsocketClientInterface $webSocket, $gameCode, $userId, $gameId, $playAi, $forGold, ?string $gameCookie ): ?string
    {
        $gameGuid   = null;
        $dbUser = $this->GetDbUser( $userId );
        if ( ! $dbUser ) {
            $this->logger->info( 'MyDebug: Missing DB User' );
            return $gameGuid;
        }
        
        $gamePlayer = $dbUser->getPlayer();
        if ( ! $gamePlayer ) {
            $this->logger->info( 'MyDebug: Missing Game Player' );
            return $gameGuid;
        }
        
        $this->logger->info( 'MyDebug Game Cookie: ' . $gameCookie );
        if ( $this->TryReConnect( $webSocket, $gameCookie, $dbUser ) ) {
            $this->logger->info( 'MyDebug: Try Reconnect' );
            // Game disconnected here
            return $gameGuid;
        }
        
        if ( ! empty ( $gameId ) ) {
            $this->logger->info( 'MyDebug: Invite to Game' );
            $this->ConnectInvite( $webSocket, $dbUser, $gameId );
            
            // Game disconnected here.
            return $gameGuid;
        }
        
        //todo: pair with someone equal ranking?
        
        // Search any game, oldest first.
        $managers = $this->orderAllGames()->filter(
            function( $entry ) {
                return ( $entry->Client2 == null || $entry->Client1 == null ) && $entry->SearchingOpponent;
            }
        );
        
        if ( self::GameAlreadyStarted( $managers, $userId ) ) {
            $warning = "MyDebug: The user {$userId} has already started a game";
            $this->logger->warning( $warning );
            throw new \Exception( $warning );
        }
        
        $isGuest = ! $gamePlayer; // $dbUser->getId() == Guid::Empty();
        // filter out games having a logged in player
        if ( $isGuest ) {
            $managers = $managers->filter(
                function( $entry ) {
                    return $entry->Game->BlackPlayer->Id != Guid::Empty() || $entry->Game->WhitePlayer->Id != Guid::Empty();
                }
            );
        }
        
        $manager = $managers->first();
        if ( $manager == null || $playAi ) {
            $manager    = $this->managerFactory->createWebsocketGameManager();
            
            if ( ! $manager->Game ) {
                $this->logger->info( "MyDebug: Creting New Game Manager." );
                $manager->Client1   = $webSocket;
                $manager->InitializeGame();
            }
            
            $manager->dispatchGameEnded();
            
            $manager->SearchingOpponent = ! $playAi;
            $manager->GameCode          = $gameCode;
            
            $gameGuid                   =  $manager->Game->Id;
            $this->AllGames->set( $gameGuid, $manager );
            $this->logger->info( "MyDebug: Added a new game and waiting for opponent. Game id {$manager->Game->Id}" );
            
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, PlayerColor::Black, $gamePlayer, $playAi );
            $this->SendConnectionLost( PlayerColor::White, $manager );
            //This is the end of the connection
            
            //$this->debugWebsockoket( $manager );
            
        } else {
            $manager->SearchingOpponent = false;
            $manager->GameCode          = $gameCode;
            
            $this->logger->info( "MyDebug: Found a game and added a second player. Game id {$manager->Game->Id}" );
            $color = $manager->Client1 == null ? PlayerColor::Black : PlayerColor::White;
            
            /*  */
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, $color, $gamePlayer, false );
            $this->logger->info( "{$color} player disconnected.");
            $this->SendConnectionLost( PlayerColor::Black, $manager );
            //This is the end of the connection
            
        }
        $this->RemoveDissconnected( $manager );
        
        return $gameGuid;
    }
    
    public function SaveState(): void
    {
        $filesystem = new Filesystem();
        $state      = $this->serializer->serialize(
            $this->AllGames,
            JsonEncoder::FORMAT,
            [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]
        );
        $filesystem->dumpFile( $projectRootDir . '/var/SavedGames.json', $state );
    }
    
    public function RestoreState(): void
    {
        $filesystem = new Filesystem();
        $now  = new \DateTime( 'now' );
        if ( $filesystem->exists( $projectRootDir . '/var/SavedGames.json' ) ) {
            $state = \file_get_contents( $projectRootDir . '/var/SavedGames.json' );
            
            $this->AllGames = $this->serializer->deserialize( $state, ArrayCollection::class, JsonEncoder::FORMAT );
            $this->AllGames = $this->AllGames->filter(
                function( $entry ) use ( $now ) {
                    return $entry->Created == $now;
                }
            );
            
            foreach ( $this->AllGames as $game ) {
                $game->setLogger( $this->logger );
            }
        }
    }
    
    public function CreateInvite( string $userId ): string
    {
        $existing = $this->AllGames->filter(
            function( $entry ) use ( $userId ) {
                return $entry->Inviter == $userId;
            }
        );
            
        for ( $i = $existing->count() - 1; $i >= 0; $i-- ) {
            $this->AllGames->removeElement( $existing[$i] );
        }
            
        $manager    = $this->managerFactory->createWebsocketGameManager();
        //$manager.Ended += Game_Ended;
        $manager->Inviter = $userId;
        $manager->SearchingOpponent = false;
        $this->AllGames[]   = $manager;
        
        return $manager->Game->Id;
    }
    
    protected function TryReConnect( WebsocketClientInterface $webSocket, ?string $gameCookie, ?UserInterface $dbUser ): bool
    {
        $getCookieUrl   = 'http://game-platform.lh/games/game-cookie';
        $response       = $this->httpClient->request( 'GET', $getCookieUrl );
        $decodedPayload = $response->toArray( false );
        
        $logData    = \print_r( $decodedPayload, true );
        $this->logger->info( "MyDebug Game Cookie: {$logData}" );
            
        // Find existing game to reconnect to.
        if ( $gameCookie ) {
            $cookie = GameCookieDto::TryParse( $gameCookie );
            if ( ! $cookie ) {
                return false;
            }
            
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
                    $gameManager->Engine = new AiEngine( $gameManager->Game );
                    $this->logger->info( "Restoring game {$cookie->id} for {$color}" );
                    
                    /*  */
                    // entering socket loop
                    $gameManager->Restore( $color, $webSocket );
                    
                    $otherColor = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                    $this->SendConnectionLost( $otherColor, $gameManager );
                    
                    // socket loop exited
                    $this->RemoveDissconnected( $gameManager );
                    
                    return true;
                }
            }
        }
        
        return false;
    }
    
    protected static function GameAlreadyStarted( Collection $managers, $userId ): bool
    {
        foreach ( $managers as $m ) {
            // Guest vs guest must be allowed. When guest games are enabled.
            if (
                $m->Game->BlackPlayer->Id == $userId ||
                $m->Game>WhitePlayer->Id == $userId &&
                $userId != Guid::Empty
                ) {
                    $this->logger->info( "MyDebug: Game Already Started" );
                    return true;
                }
        }
        
        return false;
    }
    
    protected function RemoveDissconnected( GameManagerInterface $manager ): void
    {
        if (
            ( $manager->Client1 == null || $manager->Client1->State != WebSocketState::Open ) &&
            ( $manager->Client2 == null || $manager->Client2->State != WebSocketState::Open )
        ) {
            $this->AllGames->removeElement( $manager );
            $this->logger->info( "MyDebug: Removing game {$manager->Game->Id} which is not used." );
        }
    }
    
    protected function ConnectInvite( WebsocketClientInterface $webSocket, UserInterface $dbUser, string $gameInviteId ): void
    {
        $manager = $this->AllGames->filter(
            function( $entry ) use ( $cookie ) {
                return $entry->Game->Id == $gameInviteId && ( $entry->Client1 == null || $entry->Client2 == null );
            }
        )->first();
        
        if ( $manager == null ) {
            //$webSocket->close( WebSocketCloseStatus.InvalidPayloadData, "Invite link has expired", CancellationToken.None );
            $webSocket->close();
            
            return;
        }
        
        $color = PlayerColor::Black;
        if ( $manager->Client1 != null ) {
            $color = PlayerColor::White;
        }
        
        /*  */
        $manager->ConnectAndListen( $webSocket, $color, $dbUser, false );
        
        $this->RemoveDissconnected( $manager );
        $this->SendConnectionLost( PlayerColor::White, $manager );
        
    }
    
    protected function SendConnectionLost( PlayerColor $color, GameManagerInterface $manager )
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
    
    protected static function MyColor( GameManagerInterface $manager, UserInterface $dbUser, PlayerColor $color ): bool
    {
        //prevents someone with same game id, get someone elses side in the game.
        $player = $manager->Game->BlackPlayer;
        if ( $color == PlayerColor::White ) {
            $player = $manager->Game->WhitePlayer;
        }
            
        return $dbUser != null && $dbUser->getId() == $player->Id;
    }
    
    protected function Game_Ended( object $sender ): void
    {
        $this->AllGames->removeElement( $sender );
    }
    
    protected function GetDbUser( $userId ): ?UserInterface
    {
        return $userId ? $this->usersRepository->find( $userId ) : $this->securityBridge->getUser();
    }
    
    protected function orderAllGames(): Collection
    {
        $gamesIterator  = $this->AllGames->getIterator();
        $gamesIterator->uasort( function ( $a, $b ) {
            return $a->Created > $b->Created;
        });
            
        return new ArrayCollection( \iterator_to_array( $gamesIterator ) );
    }
    
    protected function debugWebsockoket( GameManagerInterface $manager ): void
    {
        $data   = new TestWebsocketDto();
        $data->message  = 'Test Succefull.';
        
        // Test WebSocket Send
        $manager->Send( $manager->Client1, $data );
    }
}