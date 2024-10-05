<?php namespace App\Component\Type;

/**
 * Manual: https://www.php.net/manual/en/language.enumerations.backed.php
 */
enum PlayerType: string
{
    case Computer   = 'computer';
    case User       = 'user';
}
