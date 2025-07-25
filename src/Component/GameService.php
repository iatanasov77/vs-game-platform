<?php namespace App\Component;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Filesystem\Filesystem;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use Ratchet\RFC6455\Messaging\Frame;
use Vankosoft\UsersBundle\Security\SecurityBridge;

use App\Component\Manager\GameManagerInterface;
use App\Component\Manager\GameManagerFactory;
use App\Component\Manager\AbstractGameManager;
use App\Component\AI\Backgammon\EngineFactory as AiEngineFactory;
use App\Component\System\Guid;

// DTO Objects
use App\Component\Dto\GameCookieDto;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\ConnectionDto;

use App\Component\Rules\Backgammon\Helper as GameHelper;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;
use App\Entity\GamePlayer;

final class GameService
{
    use GameHelper;
    
    /** @var GameLogger */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var SecurityBridge */
    private $securityBridge;
    
    /** @var GameManagerFactory */
    private $managerFactory;
    
    /** @var Collection | GameManagerInterface[] */
    private $AllGames;
    
    public function __construct(
        GameLogger $logger,
        SerializerInterface $serializer,
        RepositoryInterface $usersRepository,
        SecurityBridge $securityBridge,
        GameManagerFactory $managerFactory
    ) {
        $this->logger           = $logger;
        $this->serializer       = $serializer;
        $this->usersRepository  = $usersRepository;
        $this->securityBridge   = $securityBridge;
        $this->managerFactory   = $managerFactory;
        
        $this->AllGames         = new ArrayCollection();
    }
    
    public function getGameManager( string $gameId ): ?GameManagerInterface
    {
        return isset( $this->AllGames[$gameId] ) ? $this->AllGames[$gameId] : null;
    }
    
    public function setGameRoomSelected( string $gameId ): ?GameManagerInterface
    {
        $this->logger->log( 'setGameRoomSelected: ' . $gameId, 'GameService' );
        if ( isset( $this->AllGames[$gameId] ) ) {
            $this->logger->log( 'Game ID Exists.', 'GameService' );
            
            $this->AllGames[$gameId]->RoomSelected  = true;
            
            return $this->AllGames[$gameId];
        }
        
        return null;
    }
    
    public function Connect(
        WebsocketClientInterface $webSocket,
        string $gameCode,
        string $gameVariant,
        int $userId,
        ?string $gameId,
        bool $playAi,
        bool $forGold,
        ?string $gameCookie
    ): ?string {
        $gameGuid   = null;
        $dbUser = $this->GetDbUser( $userId );
        if ( ! $dbUser ) {
            $this->logger->log( 'Missing DB User', 'GameService' );
            return $gameGuid;
        }
        
        $gamePlayer = $dbUser->getPlayer();
        if ( ! $gamePlayer ) {
            $this->logger->log( 'Missing Game Player', 'GameService' );
            return $gameGuid;
        }
        
        if ( $gameGuid = $this->TryReConnect( $webSocket, $gameCookie, $gamePlayer ) ) {
            $this->logger->log( 'Reconnect Game: '. $gameGuid, 'GameService' );
            // Game disconnected here
            return $gameGuid;
        }
        
        if ( ! empty ( $gameId ) ) {
            $this->logger->log( 'Invite to Game', 'GameService' );
            $gameGuid = $this->ConnectInvite( $webSocket, $gamePlayer, $gameId, $gameCode, $gameVariant );
            
            // Game disconnected here.
            return $gameGuid;
        }
        
        //todo: pair with someone equal ranking?
        
        // Search any game, oldest first.
        $managers = $this->orderAllGames( $this, AbstractGameManager::COLLECTION_ORDER_DESC )->filter(
            function( $entry ) {
                return ( $entry->Client2 == null || $entry->Client1 == null ) && $entry->SearchingOpponent;
            }
        );
        
        // Debug Found Games
        foreach( $managers as $game ) {
            $this->logger->log( "On Connect Found Game with ID: {$game->Game->Id}", 'GameService' );
        }
        
        if ( self::GameAlreadyStarted( $managers, $userId ) ) {
            $warning = "The user {$userId} has already started a game";
            $this->logger->log( $warning, 'GameService' );
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
            $this->logger->log( "Possibly Play AI !!!", 'GameService' );
            $manager            = $this->managerFactory->createWebsocketGameManager( $forGold, $gameCode, $gameVariant );
            //manager.Ended += Game_Ended;
            $manager->dispatchGameEnded();
            
            $manager->Client1   = $webSocket;
            $manager->SearchingOpponent = ! $playAi;
            $gameGuid                   =  $manager->Game->Id;
            
            $this->AllGames->set( $gameGuid, $manager );
            $this->logger->log( "Added a new game and waiting for opponent. Game id {$gameGuid}", 'GameService' );
            
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, PlayerColor::Black, $gamePlayer, $playAi );
            $this->SendConnectionLost( PlayerColor::White, $manager );
            //This is the end of the connection
            
        } else {
            $manager->SearchingOpponent = false;
            $gameGuid                   =  $manager->Game->Id;
            
            $this->logger->log( "Found a game and added a second player. Game id {$manager->Game->Id}", 'GameService' );
            $color = $manager->Client1 == null ? PlayerColor::Black : PlayerColor::White;
            $colorName = $color === PlayerColor::Black ? 'Black' : 'White';
            
            // entering socket loop
            $manager->ConnectAndListen( $webSocket, $color, $gamePlayer, false );
            $this->logger->log( "{$colorName} player disconnected.", 'GameService' );
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
    
    public function CreateInvite( int $playerId, string $gameCode, string $gameVariant ): string
    {
        $existing = $this->AllGames->filter(
            function( $entry ) use ( $playerId ) {
                return $entry->Inviter == $playerId;
            }
        );
            
        for ( $i = $existing->count() - 1; $i >= 0; $i-- ) {
            $this->AllGames->removeElement( $existing[$i] );
        }
            
        $manager = $this->managerFactory->createWebsocketGameManager( true, $gameCode, $gameVariant );
        //$manager.Ended += Game_Ended;
        $manager->dispatchGameEnded();
        
        $manager->Inviter = $playerId;
        $manager->SearchingOpponent = false;
        
        $gameGuid  = $manager->Game->Id;
        $this->AllGames->set( $gameGuid, $manager );
        $this->logger->log( "Added an Invite Game with ID: {$gameGuid}", 'GameService' );
        
        // Debug Existing Games
        foreach( $this->AllGames as $game ) {
            $this->logger->log( "On Invite Exist Game with ID: {$game->Game->Id}", 'GameService' );
        }
        
        return $gameGuid;
    }
    
    public function Game_Ended( GameManagerInterface $sender ): void
    {
        $this->logger->log( "Game_Ended for Game: {$sender->Game->Id}", 'GameService' );
        $this->AllGames->removeElement( $sender );
    }
    
    private function TryReConnect( WebsocketClientInterface $webSocket, ?string $gameCookie, ?GamePlayer $dbUser ): ?string
    {
        $this->logger->log( 'Try Reconnect with cookie: '. $gameCookie, 'GameService' );
        
        // Find existing game to reconnect to.
        if ( $gameCookie ) {
            //$cookie = GameCookieDto::TryParse( $gameCookie );
            $cookie = $this->serializer->deserialize( $gameCookie, GameCookieDto::class, JsonEncoder::FORMAT );
            $color = $cookie->color;
            
            if ( $cookie != null ) {
                $this->logger->log( 'Try Reconnect: Cookie Parsed', 'GameService' );
                
                //$json = $this->serializer->serialize( $this->AllGames, JsonEncoder::FORMAT );
                //$this->logger->log( "ReConnect GmeManagers: {$json}", 'GameService' );
                
                $gameManager = $this->AllGames->filter(
                    function( $entry ) use ( $cookie ) {
                        return $entry->Game->Id == $cookie->id && $entry->Game->PlayState != GameState::ended;
                    }
                )->first();
                
                $json = $this->serializer->serialize( $gameManager, JsonEncoder::FORMAT );
                $this->logger->log( "Found ReConnect GmeManager: {$json}", 'GameService' );
                
                if ( $gameManager && self::MyColor( $gameManager, $dbUser, $color ) ) {
                    $gameManager->Engine = AiEngineFactory::CreateBackgammonEngine(
                        $gameManager->GameCode,
                        $gameManager->GameVariant,
                        $this->logger,
                        $gameManager->Game
                    );
                    $this->logger->log( "Restoring game {$cookie->id} for {$color->value}", 'GameService' );
                    
                    // entering socket loop
                    $gameManager->Restore( $color, $webSocket );
                    
                    $otherColor = $color == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                    $this->SendConnectionLost( $otherColor, $gameManager );
                    
                    // socket loop exited
                    $this->RemoveDissconnected( $gameManager );
                    
                    return $cookie->id;
                }
            }
        }
        
        return null;
    }
    
    private function GameAlreadyStarted( Collection $managers, $userId ): bool
    {
        foreach ( $managers as $m ) {
            // Guest vs guest must be allowed. When guest games are enabled.
            if (
                $m->Game->BlackPlayer->Id == $userId ||
                $m->Game->WhitePlayer->Id == $userId &&
                $userId != Guid::Empty()
            ) {
                $this->logger->log( "Game Already Started", 'GameService' );
                return true;
            }
        }
        
        return false;
    }
    
    private function RemoveDissconnected( GameManagerInterface $manager ): void
    {
        if (
            ( $manager->Client1 == null || $manager->Client1->State != WebSocketState::Open ) &&
            ( $manager->Client2 == null || $manager->Client2->State != WebSocketState::Open )
        ) {
            $this->AllGames->removeElement( $manager );
            $this->logger->log( "Removing game {$manager->Game->Id} which is not used.", 'GameService' );
        }
    }
    
    private function ConnectInvite(
        WebsocketClientInterface $webSocket,
        GamePlayer $dbUser,
        string $gameInviteId,
        string $gameCode,
        string $gameVariant
    ): ?string {
        $manager = $this->AllGames->filter(
            function( $entry ) use ( $gameInviteId ) {
                return $entry->Game->Id == $gameInviteId && ( $entry->Client1 == null || $entry->Client2 == null );
            }
        )->first();
        
        if ( $manager == null ) {
            /* Recreate Invited Games Because When Created From a Rest Controller are Missing in Game Service
             * ==============================================================================================
            $webSocket->close( Frame::CLOSE_NORMAL );
            
            $this->logger->log( "ConnectInvite: Not Existing Game", 'GameService' );
            return null;
            */
            
            $this->logger->log( "Not Existing Game Will be Recreated", 'GameService' );
            $manager = $this->ReCreateInvite( $dbUser->getId(), $gameCode, $gameVariant, $gameInviteId );
        }
        
        $color = PlayerColor::Black;
        if ( $manager->Client1 != null ) {
            $color = PlayerColor::White;
        }
        
        $manager->ConnectAndListen( $webSocket, $color, $dbUser, false );
        
        $this->RemoveDissconnected( $manager );
        $this->SendConnectionLost( PlayerColor::White, $manager );
        
        return $manager->Game->Id;
    }
    
    private function SendConnectionLost( PlayerColor $color, GameManagerInterface &$manager )
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
            
            $this->logger->log( "SendConnectionLost for PlayerColor: {$color->value}", 'GameService' );
            $manager->Send( $socket, $action );
        }
    }
    
    private static function MyColor( GameManagerInterface $manager, GamePlayer $dbUser, PlayerColor $color ): bool
    {
        //prevents someone with same game id, get someone elses side in the game.
        $player = $manager->Game->BlackPlayer;
        if ( $color == PlayerColor::White ) {
            $player = $manager->Game->WhitePlayer;
        }
            
        return $dbUser != null && $dbUser->getId() == $player->Id;
    }
    
    private function GetDbUser( $userId ): ?UserInterface
    {
        return $userId ? $this->usersRepository->find( $userId ) : $this->securityBridge->getUser();
    }
    
    /**
     * Workaround Method To Recreate Invited Games Because When Created From a Rest Controller are Missing in Game Service
     * 
     * @TODO Maybe When Creating an Invite from a Rest Controller The Game Guid Should be Saved in Database
     *          and When Recreating here to Check if it was really created before
     * 
     * @param int $playerId
     * @param string $gameCode
     * @param string $gameVariant
     * @param string $gameInviteId
     * 
     * @return GameManagerInterface
     */
    private function ReCreateInvite( int $playerId, string $gameCode, string $gameVariant, string $gameInviteId ): GameManagerInterface
    {
        $newGuid    = $this->CreateInvite( $playerId, $gameCode, $gameVariant );
        $manager    = $this->AllGames->get( $newGuid );
        
        $manager->Game->Id = $gameInviteId;
        $this->AllGames->set( $gameInviteId, $manager );
        $this->logger->log( "ReCreate an Invite Game with ID: {$gameInviteId}", 'GameService' );
        
        $this->AllGames->remove( $newGuid );
        return $this->AllGames->get( $gameInviteId );
    }
}
