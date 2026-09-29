<?php namespace App\Component;

use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;

final class GameTeam
{
    public static function CreateGameTeam( $room ): array
    {
        $team = self::CreateEmptyGameTeam( $room );
        
        foreach ( $room->getGamePlayers() as $player ) {
            switch ( $room->getGame()->getType() ) {
                case GamePlatform::GAME_TYPE_BOARD_GAME:
                    $team[$player->getColor()][$player->getColor()] = $player;
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME:
                    if (
                        $player->getPosition() == PlayerPosition::North->toString() ||
                        $player->getPosition() == PlayerPosition::South->toString()
                    ) {
                        $team[CardGameTeam::SouthNorth->toString()][$player->getPosition()] = $player;
                    } else {
                        $team[CardGameTeam::EastWest->toString()][$player->getPosition()] = $player;
                    }
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                    $team[$player->getColor()][$player->getColor()] = $player;
                    
                    break;
                default:
                    throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
            }
        }
        
        return $team;   
    }
    
    private static function CreateEmptyGameTeam( $room ): array
    {
        switch ( $room->getGame()->getType() ) {
            case GamePlatform::GAME_TYPE_BOARD_GAME:
                $team = [
                    PlayerColor::Black->toString()  => [
                    
                    ],
                    PlayerColor::White->toString()  => [
                    
                    ],
                ];
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME:
                $team = [
                    CardGameTeam::SouthNorth->toString()  => [
                    
                    ],
                    CardGameTeam::EastWest->toString()  => [
                    
                    ],
                ];
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                $team = [
                
                ];
                
                break;
            default:
                throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
        }
        
        return $team;
    }
    
    private static function CreateEmptyGameTeamPlayer( $room ): array
    {
        
    }
}