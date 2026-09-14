<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum PlayerColor: int
{
    case Black      = 0;
    case White      = 1;
    case Neither    = 2;
    
    public function toString(): string
    {
        return match( $this ) {
            PlayerColor::Black => 'black',
            PlayerColor::White => 'white',
            PlayerColor::Neither => 'neither',
        };
    }
}
    