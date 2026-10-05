<?php namespace App\Component\WebsocketSwoole\Websocket;

use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Kernel;

use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server as WebsocketServer;

use App\Component\Logger\MyLoggerInterface;
use App\Component\WebsocketSwoole\Event\CloseEvent;
use App\Component\WebsocketSwoole\Event\MessageEvent;
use App\Component\WebsocketSwoole\Event\OpenEvent;

class Server
{
    /** @var array */
    protected const DEFAULT_OPTIONS = [
        'host' => '127.0.0.1',
        'port' => 8000,
        'mode' => 2, // SWOOLE_PROCESS
        'sock_type' => 1, // SWOOLE_SOCK_TCP
        'settings' => [],
    ];
    
    /** @var MyLoggerInterface */
    protected $logger;
    
    /** @var WebsocketServer | null */
    protected ?WebsocketServer $server;
    
    /** @var ServerHandlerInterface */
    protected ?ServerHandlerInterface $serverHandler;
    
    /** @var bool */
    protected bool $initialized;
    
    /** @var array */
    protected array $config;
    
    /** @var bool */
    protected bool $runtime;
    
    public function __construct(
        MyLoggerInterface $logger,
        array $config = []
    ) {
        $this->logger = $logger;
        
        $this->config = \array_replace_recursive( self::DEFAULT_OPTIONS, $config );
        $this->server = null;
        $this->serverHandler = null;
        
        $this->initialized = false;
    }

    public function init()
    {
        if ( $this->initialized ) {
            return;
        }

        $this->setServer( new WebsocketServer( $this->config['host'], $this->config['port'], SWOOLE_PROCESS ), true );
        $this->server->set( $this->config['settings'] );
    }

    public function setServer( WebsocketServer $server, bool $setInitialized = true ): void
    {
        $this->server = $server;
        if ( $setInitialized ) {
            $this->initialized = true;
        }
    }

    public function setEvent(): void
    {
        $this->server->on( OpenEvent::NAME, [$this->serverHandler, 'onOpen'] );
        $this->server->on( MessageEvent::NAME, [$this->serverHandler, 'onMessage'] );
        $this->server->on( CloseEvent::NAME, [$this->serverHandler, 'onClose'] );
    }

    public function on( string $event, callable $callable ): void
    {
        $this->server->on( $event, $callable );
    }

    public function start(): void
    {
        $this->server->start();
    }

    public function getServer(): WebsocketServer
    {
        return $this->server;
    }

    public function setHost( string $host ): void
    {
        $this->config['host'] = $host;
    }

    public function setPort( int $port ): void
    {
        $this->config['port'] = $port;
    }
    
    public function setHandler( ServerHandlerInterface $handler ): void
    {
        $this->serverHandler = $handler;
    }
}
