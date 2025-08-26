<?php namespace App\Component\AI\BoardGame;

use App\Component\GameVariant;
use App\Component\GameLogger;
use App\Component\Rules\BoardGame\Game;

final class EngineFactory
{
    public static function CreateBoardGameEngine( string $gameCode, string $gameVariant, GameLogger $logger, Game $game ): Engine
    {
        switch ( $gameCode ) {
            case GameVariant::BACKGAMMON_GULBARA:
                $engine = self::CreateBackgammonEngine( $gameVariant, $logger, $game );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        return $engine;
    }
    
    private static function CreateBackgammonEngine( string $gameVariant, GameLogger $logger, Game $game ): Engine
    {
        switch ( $gameVariant ) {
            case GameVariant::BACKGAMMON_NORMAL:
                $engine = new BackgammonNormalEngine( $logger, $game );
                break;
            case GameVariant::BACKGAMMON_TAPA:
                $engine = new BackgammonTapaEngine( $logger, $game );
                break;
            case GameVariant::BACKGAMMON_GULBARA:
                $engine = new BackgammonGulBaraEngine( $logger, $game );
                break;
            default:
                throw new \RuntimeException( 'Unknown Backgammon Variant !!!' );
        }
        
        return $engine;
    }
}
