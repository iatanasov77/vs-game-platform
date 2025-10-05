<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;

class PlayerPositionExtensions
{
    public static function Next( PlayerPosition $playerPosition ): PlayerPosition
    {
        switch ( $playerPosition ) {
            case PlayerPosition::South:
                return PlayerPosition::East;
                break;
            case PlayerPosition::East:
                return PlayerPosition::North;
                break;
            case PlayerPosition::North:
                return PlayerPosition::West;
                break;
            case PlayerPosition::West:
                return PlayerPosition::South;
                break;
            default:
                throw new \RuntimeException( "Invalid player position." );
        }
    }
    
    public static function Index( PlayerPosition $playerPosition ): int
    {
        switch ( $playerPosition ) {
            case PlayerPosition::South:
                return 0;
                break;
            case PlayerPosition::East:
                return 1;
                break;
            case PlayerPosition::North:
                return 2;
                break;
            case PlayerPosition::West:
                return 3;
                break;
            default:
                throw new \RuntimeException( "Invalid player position." );
        }
    }
    
    public static function IsInSameTeamWith( PlayerPosition $position, PlayerPosition $otherPlayerPosition ): bool
    {
        return ( $position == PlayerPosition::South && $otherPlayerPosition == PlayerPosition::North )
                || ( $position == PlayerPosition::North && $otherPlayerPosition == PlayerPosition::South )
                || ( $position == PlayerPosition::East && $otherPlayerPosition == PlayerPosition::West )
                || ( $position == PlayerPosition::West && $otherPlayerPosition == PlayerPosition::East )
                || ( $position == $otherPlayerPosition );
    }
    
    
    public static function GetTeammate( PlayerPosition $playerPosition ): PlayerPosition
    {
        switch ( $playerPosition ) {
            case PlayerPosition::South:
                return PlayerPosition::North;
                break;
            case PlayerPosition::East:
                return PlayerPosition::West;
                break;
            case PlayerPosition::North:
                return PlayerPosition::South;
                break;
            case PlayerPosition::West:
                return PlayerPosition::East;
                break;
            default:
                throw new \RuntimeException( "Invalid player position." );
        }
    }
}
