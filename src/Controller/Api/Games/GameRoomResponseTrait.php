<?php namespace App\Controller\Api\Games;

use App\Component\GamePlatform;
use App\Entity\GamePlay;

trait GameRoomResponseTrait
{
    private function createResponseBody( GamePlay $gameRoom, array $roomTeams = [] ): array
    {
        $data = [
            'id'        => $gameRoom->getId(),
            'owner'     => $gameRoom->getOwner()->getName(),
            'guid'      => $gameRoom->getGuid(),
            'name'      => $gameRoom->getGuid(),
            
            'game'      => $this->_getRoomGame( $gameRoom ),
            'players'   => $this->_getRoomPlayers( $gameRoom ),
            'teams'     => $roomTeams,
            
            'status'    => $gameRoom->getStatus(),
            'isPlaying' => $gameRoom->getStatus() == GamePlatform::GAME_ROOM_STATUS_PLAYING,
        ];
        
        return $data;
    }
    
    private function _getRoomGame( GamePlay $room ): array
    {
        $game   = [
            'id'            => $room->getGame()->getId(),
            'slug'          => $room->getGame()->getSlug(),
            'title'         => $room->getGame()->getTitle(),
            'maxPlayers'    => $room->getGame()->getMaxPlayers(),
        ];
        
        return $game;
    }
    
    private function _getRoomPlayers( GamePlay $room ): array
    {
        $players = [];
        foreach ( $room->getGamePlayers() as $player ) {
            $players[] = [
                'id'    => $player->getId(),
                'type'  => $player->getType(),
                'name'  => $player->getName(),
            ];
        }
        
        return $players;
    }
}
