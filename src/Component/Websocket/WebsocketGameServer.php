<?php namespace App\Component\Websocket;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Psr\Container\ContainerInterface;
use Vankosoft\ApplicationBundle\Command\ContainerAwareCommand;

use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Ratchet\MessageComponentInterface;
use React\EventLoop\Factory as EventLoopFactory;
use React\Socket\SocketServer;
use App\Component\GameService;
use App\Component\GameLogger;
use App\Component\Websocket\Server\WebsocketGamesHandler;

/**
 * See Logs:        sudo tail -f /dev/shm/game-platform.lh/game-platform/log/websocket_game.log
 * Start Service:   sudo service websocket_game_platform_game restart
 *
 * Forked From: https://www.codeproject.com/Articles/5297405/Online-Backgammon
 * Play Original Game: https://backgammon.azurewebsites.net/
 *
 * Manual:  https://stackoverflow.com/questions/64292868/how-to-send-a-message-to-specific-websocket-clients-with-symfony-ratchet
 *          https://stackoverflow.com/questions/30953610/how-to-send-messages-to-particular-users-ratchet-php-websocket
 */

/**
 * Forked From: https://www.codeproject.com/Articles/5297405/Online-Backgammon
 * Play Original Game: https://backgammon.azurewebsites.net/
 */
#[AsCommand(
    name: 'vgp:websocket:game',
    description: 'Start WebSocket for Game',
    hidden: false
)]
final class WebsocketGameServer extends ContainerAwareCommand
{
    /** @var GameService */
    private $gameService;
    
    /** @var GameLogger */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var MessageComponentInterface */
    private $gamesHandler;
    
    /** @var array */
    private $parrameters;
    
    public function __construct(
        ContainerInterface $container,
        ManagerRegistry $doctrine,
        ValidatorInterface $validator,
        GameService $gameService,
        GameLogger $logger,
        SerializerInterface $serializer,
        array $parrameters
    ) {
        parent::__construct( $container, $doctrine, $validator );
        
        $this->gameService  = $gameService;
        $this->logger       = $logger;
        $this->serializer   = $serializer;
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
        //$this->logger->log( "Possix Signal: " . $signo, 'GameServer' );
        
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
            $this->logger,
            $this->serializer,
            $this->get( 'vs_users.repository.users' ),
            $this->get( 'app_websocket_client_factory' ),
            $this->gameService,
            $this->parrameters['logExceptionTrace']
        );
        
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
                    $this->gamesHandler
                )
            ),
            $socketServer,
            $loop
        );
        
        $loop->run();
        
        return Command::SUCCESS;
    }
}
