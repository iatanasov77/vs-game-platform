<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum PlayerPosition: int
{
    case North      = 0;
    case South      = 1;
    case East       = 2;
    case West       = 3;
    case Neither    = 4;
    
    public function toString(): string
    {
        return match( $this ) {
            PlayerPosition::North => 'north',
            PlayerPosition::South => 'south',
            PlayerPosition::East => 'east',
            PlayerPosition::West => 'west',
        };
    }
}
