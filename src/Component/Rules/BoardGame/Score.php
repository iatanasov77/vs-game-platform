<?php namespace App\Component\Rules\BoardGame;

class Score
{
    public static function NewScore( int $blackScr, int $whiteScr, int $blackGames, int $whiteGames, bool $blackWon ): array
    {
        $blackK = self::GetK( $blackGames );
        $whiteK = self::GetK( $whiteGames );
        
        return self::EloRating( $blackScr, $whiteScr, $blackK, $whiteK, $blackWon );
    }
    
    public static function Probability( float $rating1, float $rating2 ): float
    {
        return 1 / ( 1 + \pow( 10, ( $rating1 - $rating2 ) / 400 ) );
    }
    
    public static function EloRating( float $black, float $white, float $blackK, float $whiteK, bool $blackWon ): array
    {
        $whiteProb = self::Probability( $black, $white );
        $blackProb = self::Probability( $white, $black );
        
        if ( $blackWon == true ) {
            $black = $black + $blackK * ( 1 - $blackProb );
            $white = $white + $whiteK * ( 0 - $whiteProb );
        } else {
            $black = $black + $blackK * ( 0 - $blackProb );
            $white = $white + $whiteK * ( 1 - $whiteProb );
        }
        $b = ( int ) \round( $black );
        $w = ( int ) \round( $white );
        
        return [
            'black' => $b,
            'white' => $w
        ];
    }
    
    private static function GetK( int $games ): float
    {
        return 85 * \exp( -0.1 * $games ) + 15;
    }
}
