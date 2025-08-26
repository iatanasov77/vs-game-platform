<?php namespace App\Component\AI\CardGame;

use App\Component\GameVariant;
use App\Component\GameLogger;
use App\Component\Rules\CardGame\Game;

final class EngineFactory
{
    public static function CreateCardGameEngine( string $gameCode, ?string $gameVariant, GameLogger $logger, Game $game ): Engine
    {
        switch ( $gameCode ) {
            case GameVariant::BRIDGE_BELOTE_CODE:
                $engine = new BridgeBeloteEngine( $logger, $game );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        return $engine;
    }
}
