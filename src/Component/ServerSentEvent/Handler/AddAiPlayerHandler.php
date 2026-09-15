<?php namespace App\Component\ServerSentEvent\Handler;

use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

use App\Component\MercureLogger;
use App\Component\ServerSentEvent\Message\AddAiPlayerMessage;

#[AsMessageHandler]
class AddAiPlayerHandler
{
    /** @var MercureLogger */
    private $logger;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct(
        MercureLogger $logger,
        HubInterface $hub
    ) {
        $this->logger   = $logger;
        $this->hub      = $hub;
    }
    
    public function __invoke( AddAiPlayerMessage $message )
    {
        $lobbyId = $message->getLobbyId();
        // var_dump( $lobbyId ); die;
        
        // 1. Simulate human latency (makes the bot feel real)
        \usleep( \rand( 500000, 1500000 ) );
        
        // 2. Fetch current lobby from Redis & add the AI player
        // $lobby = $this->redis->get($lobbyId);
        $aiPlayer = [
            'id' => 'ai-' . uniqid(),
            'name' => 'Bot_' . ['Alpha', 'Bravo', 'Charlie'][array_rand(['Alpha', 'Bravo', 'Charlie'])],
            'is_ai' => true
        ];
        // $lobby['players'][] = $aiPlayer;
        // $this->redis->set($lobbyId, $lobby);
        
        // 3. Broadcast update to all connected frontend clients via Mercure
        $update = new Update(
            "https://yourdomain.com{$lobbyId}",
            \json_encode( ['event' => 'player_joined', 'player' => $aiPlayer] )
        );
        
        $this->hub->publish( $update );
    }
}
