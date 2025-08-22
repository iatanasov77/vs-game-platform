<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum PlayerPosition: string
{
    case North  = 'north';
    case South  = 'south';
    case East   = 'east';
    case West   = 'west';
}
