<?php namespace App\Component;

use App\Component\Dto\GameRoomDto;
use App\Component\Dto\GameDto;
use App\Component\Dto\PlayerDto;

use App\Component\GamePlatform;
use App\Entity\GamePlay;

trait GameRoomObjectTrait
{
    private function createResponseBody( GamePlay $gameRoom, array $roomTeams = [] ): GameRoomDto
    {
        $room = new GameRoomDto();
        
        $room->id           = $gameRoom->getId();
        $room->owner        = $gameRoom->getOwner()->getName();
        $room->guid         = $gameRoom->getGuid();
        $room->name         = $gameRoom->getGuid();
        
        $room->game         = $this->_getRoomGame( $gameRoom );
        $room->players      = $this->_getRoomPlayers( $gameRoom );
        $room->teams        = $roomTeams;
        
        $room->status       = $gameRoom->getStatus();
        $room->isPlaying    = $gameRoom->getStatus() == GamePlatform::GAME_ROOM_STATUS_PLAYING;
        
        return $room;
    }
    
    private function _getRoomGame( GamePlay $room ): GameDto
    {
        $game = new GameDto();
        
        $game->id           = $room->getGuid();
        $game->gameCode     = $room->getGame()->getSlug();
        $game->title        = $room->getGame()->getTitle();
        $game->maxPlayers   = $room->getGame()->getMaxPlayers();
        
        return $game;
    }
    
    private function _getRoomPlayers( GamePlay $room ): array
    {
        $players = [];
        foreach ( $room->getGamePlayers() as $player ) {
            $playerDto = new PlayerDto();
            
            $playerDto->id      = $player->getId();
            $playerDto->type    = $player->getType();
            $playerDto->name    = $player->getName();
            
            $players[] = $playerDto;
        }
        
        return $players;
    }
    
    private function _getRoomTeams( GamePlay $room ): array
    {
        $teamsResponse = [];
        if ( ! \property_exists( $this, 'gameTeamService' ) ) {
            return $teamsResponse;
        }
        
        $teams = $this->gameTeamService->createGameTeam( $room );
        foreach ( $teams as $team ) {
            $teamsResponse[] = $team;
        }
        
        return $teamsResponse;
    }
}
