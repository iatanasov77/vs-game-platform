<?php namespace App\Component;

use App\Component\Type\PlayerPosition;

class PlayerPositions
{
    const PlayerPositions   = [
        PlayerPosition::South,
        PlayerPosition::West,
        PlayerPosition::North,
        PlayerPosition::East
    ];
    
    public static function Next( PlayerPosition $playerPosition ): PlayerPosition
    {
        $PlayerPositionIndex        = array_search( $playerPosition, self::PlayerPositions );
        $NextPlayerPositionIndex    = ( $PlayerPositionIndex + 1 ) % count( self::PlayerPositions );
        
        return self::PlayerPositions[$NextPlayerPositionIndex];
    }
    
    public static function Prev( PlayerPosition $playerPosition ): PlayerPosition
    {
        $PlayerPositionIndex        = array_search( $playerPosition, self::PlayerPositions );
        $NextPlayerPositionIndex    = ( $PlayerPositionIndex - 1 ) % count( self::PlayerPositions );
        
        if ( $NextPlayerPositionIndex < 0 ) {
            $playerPositions = self::PlayerPositions;
            $playerPosition = \end( $playerPositions );
        } else {
            $playerPosition = self::PlayerPositions[$NextPlayerPositionIndex];
        }
        
        return $playerPosition;
    }
}
