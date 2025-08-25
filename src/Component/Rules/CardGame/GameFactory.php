<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use App\Component\GameLogger;
use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;

final class GameFactory
{
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger   = $logger;
    }
    
    public function createBridgeBeloteGame( bool $forGold ): Game
    {
        $game = new BridgeBeloteGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        
        $game->NorthPlayer = new Player();
        $game->NorthPlayer->PlayerPosition = PlayerPosition::North;
        $game->NorthPlayer->Name = "Guest";
        
        $game->WestPlayer = new Player();
        $game->WestPlayer->PlayerPosition = PlayerPosition::West;
        $game->WestPlayer->Name = "Guest";
        
        $game->SouthPlayer = new Player();
        $game->SouthPlayer->PlayerPosition = PlayerPosition::South;
        $game->SouthPlayer->Name = "Guest";
        
        $game->EastPlayer = new Player();
        $game->EastPlayer->PlayerPosition = PlayerPosition::East;
        $game->EastPlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        
        $game->SetStartPosition();
        
        return $game;
    }
}
