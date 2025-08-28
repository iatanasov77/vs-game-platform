<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Entity\GamePlayer;

interface GameManagerInterface
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void;
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void;
    public function StartGame(): void;
}
