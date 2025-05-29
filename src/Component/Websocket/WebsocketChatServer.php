<?php namespace App\Component\Websocket;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Vankosoft\ApplicationBundle\Command\ContainerAwareCommand;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\SocketServer;
use App\Component\Websocket\Server\WebsocketMessageHandler;

/**
 * See Logs:        sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket.log
 * Start Service:   sudo service websocket_game_platform_chat restart
 *
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
#[AsCommand(
    name: 'vgp:websocket:chat',
    description: 'Start WebSocket Chat',
    hidden: false
)]
final class WebsocketChatServer extends ContainerAwareCommand
{
    /** @var LoggerInterface */
    private $logger;
    
    /** @var array */
    private $parrameters;
    
    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $doctrine,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        array $parrameters
    ) {
        parent::__construct( $container, $doctrine, $validator );
        
        $this->logger       = $logger;
        $this->parrameters  = $parrameters;
    }
    
    /**
     * possix signal handler function
     */
    public function sigHandler( $signo )
    {
        /**
         * @NOTE POSSIX SIGNAL CODES: https://www.php.net/manual/en/pcntl.constants.php#115603
         */
        //$this->log( "Possix Signal: " . $signo );
        
        switch ( $signo ) {
            case SIGTERM:
                //$this->gamesHandler->serverWasTerminated();
                exit;
                break;
            case SIGHUP:
                // handle restart tasks
                break;
            default:
                // handle all other signals
        }
    }
    
    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setHelp( 'The <info>%command.name%</info> starts the GamePlatform WebSocket Chat Server.' )
            ->addArgument( 'port', InputArgument::REQUIRED, 'The port of the server you\'re starting' );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        \pcntl_signal( SIGTERM, array( $this, 'sigHandler' ) );
        \pcntl_signal( SIGHUP, array( $this, 'sigHandler' ) );
        
        $port   = $input->getArgument( 'port' );
        
        $messageHandler = new WebsocketMessageHandler();
        $loop           = EventLoopFactory::create();
        $socketServer   = new SocketServer( '0.0.0.0:' . $port, [
            'local_cert'        => $this->parrameters['sslCertificateCert'],
            'local_pk'          => $this->parrameters['sslCertificateKey'],
            'allow_self_signed' => true,
            'verify_peer'       => false
        ], $loop );
        
        $websocketServer = new IoServer(
            new HttpServer(
                new WsServer(
                    $messageHandler
                )
            ),
            $socketServer,
            $loop
        );
        
        $loop->run();
        
        return Command::SUCCESS;
    }
    
    private function log( $logData ): void
    {
        //\file_put_contents( $this->logFile, $logData . "\n", FILE_APPEND | LOCK_EX );
        $this->logger->info( $logData );
    }
}
