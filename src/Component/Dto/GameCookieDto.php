<?php namespace App\Component\Dto;

use App\Component\Type\PlayerColor;

/// <summary>
/// Stored as a cookie on the client to enable reconnects.
/// </summary>
class GameCookieDto
{
    /** @var string */
    public string $id;
    
    /** @var PlayerColor */
    public PlayerColor $color;
    
    /** @var string */
    public string $game;
    
    public static function TryParse( ?string $v ): ?GameCookieDto
    {
        if ( $v ) {
            return \json_decode( $v, false );
        }
        
        return null;
    }
    
    public function __toString(): string
    {
        return $this->id + " " + $this->color;
    }
}
