<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;
use App\Entity\GamePlayer;

interface GameManagerInterface
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, PlayerColor $color, GamePlayer $dbUser, bool $playAi ): void;
}
