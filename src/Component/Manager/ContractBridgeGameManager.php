<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerPosition;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Entity\GamePlayer;
use App\Component\System\Guid;
use App\Component\Websocket\WebSocketState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\GameRestoreActionDto;

class ContractBridgeGameManager extends AbstractGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        
    }
    
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        
    }
}
