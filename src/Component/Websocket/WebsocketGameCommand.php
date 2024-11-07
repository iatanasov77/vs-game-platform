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
use Symfony\Component\Serializer\SerializerInterface;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\Server as SocketServer;
use App\Component\Websocket\Server\WebsocketGamesHandler;

/**
 * See Logs:        sudo tail -f /var/log/websocket/game-patform-game.log
 * Start Service:   sudo service websocket_game_platform_game restart
 *
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */
#[AsCommand(
    name: 'vgp:websocket:game',
    description: 'Start WebSocket Game',
    hidden: false
)]
final class WebsocketGameCommand extends ContainerAwareCommand
{
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var string */
    private $logFile;
    
    /** @var MessageComponentInterface */
    private $gamesHandler;
    
    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $doctrine,
        ValidatorInterface $validator,
        SerializerInterface $serializer
    ) {
        parent::__construct( $container, $doctrine, $validator );
        
        $this->serializer   = $serializer;
        $this->logFile      = '/var/log/websocket/game-patform-game.log';
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
                $this->gamesHandler->serverWasTerminated();
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
            ->setHelp( 'The <info>%command.name%</info> starts the GamePlatform WebSocket Game Server.' )
            ->addArgument( 'port', InputArgument::REQUIRED, 'The port of the server you\'re starting' );
    }
    
    /**
     * {@inheritdoc}
     */
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        \pcntl_signal( SIGTERM, array( $this, 'sigHandler' ) );
        \pcntl_signal( SIGHUP, array( $this, 'sigHandler' ) );
        
        $port = $input->getArgument( 'port' );
        
        $this->gamesHandler = new WebsocketGamesHandler(
            $this->serializer,
            $this->get( 'vs_users.repository.users' ),
            $this->get( 'app_websocket_client_factory' ),
            $this->get( 'app_game_service' )
        );
        
        $loop           = EventLoopFactory::create();
        $socketServer   = new SocketServer( '0.0.0.0:' . $port, $loop );
        
        $websocketServer = new IoServer(
            new HttpServer(
                new WsServer(
                    $this->gamesHandler
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
        \file_put_contents( $this->logFile, $logData . "\n", FILE_APPEND | LOCK_EX );
    }
}
