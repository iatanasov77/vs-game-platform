<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum PlayerColor: string
{
    case Black      = 'black';
    case White      = 'white';
    case Neither    = 'neither';
}
    