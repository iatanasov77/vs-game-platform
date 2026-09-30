<?php namespace App\Component;

use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;

final class GameTeam
{
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    public function __construct(
        LiipImagineCacheManager $imagineCacheManager,
    ) {
        $this->imagineCacheManager = $imagineCacheManager;
    }
    
    public function createGameTeam( $room ): array
    {
        $team = $this->createEmptyGameTeam( $room );
        
        foreach ( $room->getGamePlayers() as $player ) {
            switch ( $room->getGame()->getType() ) {
                case GamePlatform::GAME_TYPE_BOARD_GAME:
                    $team[$player->getColor()][$player->getColor()] = new GameTeamPlayer( $player, $this->imagineCacheManager );
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME:
                    if (
                        $player->getPosition() == PlayerPosition::North->toString() ||
                        $player->getPosition() == PlayerPosition::South->toString()
                    ) {
                        $team[CardGameTeam::SouthNorth->toString()][$player->getPosition()] = new GameTeamPlayer( $player, $this->imagineCacheManager );
                    } else {
                        $team[CardGameTeam::EastWest->toString()][$player->getPosition()] = new GameTeamPlayer( $player, $this->imagineCacheManager );
                    }
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                    $team[$player->getColor()][$player->getColor()] = new GameTeamPlayer( $player, $this->imagineCacheManager );
                    
                    break;
                default:
                    throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
            }
        }
        
        return $team;   
    }
    
    private function createEmptyGameTeam( $room ): array
    {
        switch ( $room->getGame()->getType() ) {
            case GamePlatform::GAME_TYPE_BOARD_GAME:
                $team = [
                    PlayerColor::Black->toString()  => [
                        PlayerColor::Black->toString() => new GameTeamPlayer(),
                    ],
                    PlayerColor::White->toString()  => [
                        PlayerColor::White->toString() => new GameTeamPlayer(),
                    ],
                ];
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME:
                $team = [
                    CardGameTeam::SouthNorth->toString()  => [
                        PlayerPosition::South->toString() => new GameTeamPlayer(),
                        PlayerPosition::North->toString() => new GameTeamPlayer(),
                    ],
                    CardGameTeam::EastWest->toString()  => [
                        PlayerPosition::East->toString() => new GameTeamPlayer(),
                        PlayerPosition::West->toString() => new GameTeamPlayer(),
                    ],
                ];
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                $team = [
                    new GameTeamPlayer(),
                ];
                
                break;
            default:
                throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
        }
        
        return $team;
    }
    
    private function createEmptyGameTeamPlayer( $room ): array
    {
        
    }
}