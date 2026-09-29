<?php namespace App\Component\Type;

enum CardGameTeam: int
{
    case SouthNorth = 0;
    case EastWest   = 1;
    case Neither    = 2;
    
    public function toString(): string
    {
        return match( $this ) {
            CardGameTeam::SouthNorth  => 'south_north',
            CardGameTeam::EastWest    => 'east_west',
            CardGameTeam::Neither     => 'neither',
        };
    }
}
