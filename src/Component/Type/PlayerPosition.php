<?php namespace App\Component\Type;

/**
 *  N
 * W E
 *  S
 */
enum PlayerPosition: int
{
    case South      = 0;
    case East       = 1;
    case North      = 2;
    case West       = 3;
    case Neither    = 4;
    
    public function toString(): string
    {
        return match( $this ) {
            PlayerPosition::South => 'south',
            PlayerPosition::East => 'east',
            PlayerPosition::North => 'north',
            PlayerPosition::West => 'west',
        };
    }
}
