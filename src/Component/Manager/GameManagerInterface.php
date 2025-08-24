<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Entity\GamePlayer;

interface GameManagerInterface
{
    public function ConnectAndListenBoardGame( WebsocketClientInterface $webSocket, PlayerColor $color, GamePlayer $dbUser, bool $playAi ): void;
    public function RestoreBoardGame( PlayerColor $color, WebsocketClientInterface $socket ): void;
    public function ConnectAndListenCardGame( WebsocketClientInterface $webSocket, PlayerPosition $position, GamePlayer $dbUser, bool $playAi ): void;
    public function RestoreCardGame( PlayerPosition $position, WebsocketClientInterface $socket ): void;
}
