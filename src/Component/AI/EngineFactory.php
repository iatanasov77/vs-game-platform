<?php namespace App\Component\AI;

use App\Component\GameVariant;
use App\Component\GameLogger;
use App\Component\Rules\GameInterface;

// Game AI Engines
use App\Component\AI\BoardGame\BackgammonNormalEngine;
use App\Component\AI\BoardGame\BackgammonTapaEngine;
use App\Component\AI\BoardGame\BackgammonGulBaraEngine;
use App\Component\AI\CardGame\BridgeBeloteEngine;

final class EngineFactory
{
    public static function CreateAiEngine( string $gameCode, ?string $gameVariant, GameLogger $logger, GameInterface $game ): AiEngineInterface
    {
        switch ( $gameCode ) {
            case GameVariant::BACKGAMMON_CODE:
                $engine = self::CreateBackgammonEngine( $gameVariant, $logger, $game );
                break;
            case GameVariant::BRIDGE_BELOTE_CODE:
                $engine = new BridgeBeloteEngine( $logger, $game );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        return $engine;
    }
    
    private static function CreateBackgammonEngine( string $gameVariant, GameLogger $logger, GameInterface $game ): AiEngineInterface
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
