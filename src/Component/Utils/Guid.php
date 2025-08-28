<?php namespace App\Component\Utils;

final class Guid
{
    public static function Empty()
    {
        return '00000000-0000-0000-0000-000000000000';
    }
    
    public static function NewGuid()
    {
        return \sprintf(
            '%04X%04X-%04X-%04X-%04X-%04X%04X%04X',
            \mt_rand( 0, 65535 ),
            \mt_rand( 0, 65535 ),
            \mt_rand( 0, 65535 ),
            \mt_rand( 16384, 20479 ),
            \mt_rand( 32768, 49151 ),
            \mt_rand( 0, 65535 ),
            \mt_rand( 0, 65535 ),
            \mt_rand( 0, 65535 )
        );
    }
}
