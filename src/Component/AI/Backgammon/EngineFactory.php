<?php namespace App\Component\AI\Backgammon;

use App\Component\Utils\Keys;
use App\Component\GameLogger;
use App\Component\Rules\Backgammon\Game;

final class EngineFactory
{
    public static function CreateBackgammonEngine( string $gameCode, string $gameVariant, GameLogger $logger, Game $game ): Engine
    {
        switch ( $gameVariant ) {
            case Keys::BACKGAMMON_NORMAL_KEY:
                $engine = new BackgammonNormalEngine( $logger, $game );
                break;
            case Keys::BACKGAMMON_TAPA_KEY:
                $engine = new BackgammonTapaEngine( $logger, $game );
                break;
            case Keys::BACKGAMMON_GULBARA_KEY:
                $engine = new BackgammonGulBaraEngine( $logger, $game );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        return $engine;
    }
}
