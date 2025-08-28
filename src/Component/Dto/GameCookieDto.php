<?php namespace App\Component\Dto;

use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

/**
 * Stored as a cookie on the client to enable reconnects.
 */
class GameCookieDto
{
    /** @var string */
    public string $id;
    
    /** @var string */
    public string $game;
    
    /** @var PlayerColor */
    public PlayerColor $color;
    
    /** @var PlayerPosition */
    public PlayerPosition $position;
    
//     public static function TryParse( ?string $v ): ?GameCookieDto
//     {
//         if ( $v ) {
//             return \json_decode( $v, false );
//         }
        
//         return null;
//     }
    
    public function __toString(): string
    {
        return $this->id + " " + $this->color;
    }
}
