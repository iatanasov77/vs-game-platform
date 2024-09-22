<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Dice
{
    /** @var int */
    public $Value;
    
    /** @var bool */
    public $Used;

    /** @var int */
    private static $Random;

    public static function RollOne(): int
    {
        return \rand( 1, 7 );
    }

    public static function Roll(): Collection
    {
        $val1 = self::RollOne();
        $val2 = self::RollOne();
        
        return GetDices( $val1, $val2 );
    }

    public static function GetDices( int $val1, int $val2 ): Collection
    {
        if ( $val1 == $val2 ) {
            return new ArrayCollection([
                ( ( new Dice )->Value = $val1 ),
                ( ( new Dice )->Value = $val1 ),
                ( ( new Dice )->Value = $val1 ),
                ( ( new Dice )->Value = $val1 ),
            ]);
        }

        return new ArrayCollection([
            ( ( new Dice )->Value = $val1 ),
            ( ( new Dice )->Value = $val2 ),
        ]);
    }

    public function __toString(): string
    {
        return $this->Value . ( $this->Used ? " Used" : " Not used" );
    }
}
