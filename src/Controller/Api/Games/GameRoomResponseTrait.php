<?php namespace App\Controller\Api\Games;

use App\Entity\GamePlay;

trait GameRoomResponseTrait
{
    private function createResponseBody( GamePlay $gameRoom ): array
    {
        $data = [
            'id'    => $gameRoom->getId(),
            'room'  => [
                'id'        => $gameRoom->getId(),
                'players'   => [],
            ],
        ];
        
        foreach ( $gameRoom->getGamePlayers() as $player ) {
            $data['room']['players'][] = [
                'id'            => $player->getGuid(),
                'containerId'   => $player->getPosition(),
                'name'          => $player->getName(),
                'type'          => $player->getType(),
            ];
        }
        
        return $data;
    }
}
