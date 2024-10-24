<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Psr\Log\LoggerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

// Events
use App\EventListener\WebsocketEvent\MessageEvent;
use App\EventListener\WebsocketEvent\GameEndedEvent;

use App\Component\Websocket\WebSocketState;
use App\Component\Dto\Actions\ActionDto;
use App\Component\Dto\Actions\ActionNames;

final class WebsocketEventSubscriber implements EventSubscriberInterface
{
    /** @var LoggerInterface */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    public function __construct(
        LoggerInterface $logger,
        SerializerInterface $serializer,
        ManagerRegistry $doctrine,
        RepositoryInterface $gamePlayRepository
    ) {
        $this->logger               = $logger;
        $this->serializer           = $serializer;
        $this->doctrine             = $doctrine;
        $this->gamePlayRepository   = $gamePlayRepository;
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            MessageEvent::NAME      => 'onMessageEvent',
            GameEndedEvent::NAME    => 'onGameEnded',
        ];
    }
    
    public function onMessageEvent( MessageEvent $event ): void
    {
        $socket = $event->getWebsocketClient();
        
        // , [JsonEncode::OPTIONS => JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT]
        $json   = $this->serializer->serialize( $socket->State, JsonEncoder::FORMAT );
        $this->logger->info( "MyDebug: Websocket Message Emited. Socket State: " . $json );
        
        if ( $socket->State != WebSocketState::Open ) {
            return;
        }
        
        $manager    = $event->getGameManager();
        $text = $event->getWebsocketMessage();
        
        if ( $text != null && ! empty( $text ) ) {
            $this->logger->info( "MyDebug WebSocket Received: {$text}" );
            
            try {
                $action         = $this->serializer->deserialize( $text, ActionDto::class, JsonEncoder::FORMAT );
                $otherClient    = $socket == $manager->Client1 ? $manager->Client2 : $manager->Client1;
                
                $manager->DoAction( ActionNames::from( $action->actionName ), $text, $socket, $otherClient );
            } catch ( \Exception $e ) {
                $this->logger->info( "MyDebug: Failed to parse Action text {$e->getMessage()}" );
            }
        }
    }
    
    public function onGameEnded( GameEndedEvent $event ): void
    {
        $this->logger->info( "MyDebug: Game Ended Event Emited" );
        
        $gameDto        = $event->getSubject();
        $gameSession    = $this->gamePlayRepository->findOneBy( ['guid' => $gameDto->id] );
        $em             = $this->doctrine->getManager();
        
        if ( $gameSession ) {
            $em->remove( $gameSession );
            $em->flush();
        }
    }
}
