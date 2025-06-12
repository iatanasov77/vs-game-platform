<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

class Dice
{
    /** @var int */
    public $Value;
    
    /** @var bool */
    public $Used = false;
    
    /** @var int */
    private static $Random;
    
    public static function RollOne(): int
    {
        return \rand( 1, 6 );
    }
    
    public static function Roll(): Collection
    {
        $val1 = self::RollOne();
        $val2 = self::RollOne();
        
        return self::GetDices( $val1, $val2 );
    }
    
    public static function GetDices( int $val1, int $val2 ): Collection
    {
        if ( $val1 == $val2 ) {
            $dice   = new Dice();
            $dice->Value    = $val1;
            
            return new ArrayCollection([
                clone $dice,
                clone $dice,
                clone $dice,
                clone $dice,
            ]);
        }
        
        $dice1   = new Dice();
        $dice1->Value    = $val1;
        
        $dice2   = new Dice();
        $dice2->Value    = $val2;
        
        return new ArrayCollection([
            $dice1,
            $dice2,
        ]);
    }
    
    public function __toString(): string
    {
        return $this->Value . ( $this->Used ? " Used" : " Not used" );
    }
}
