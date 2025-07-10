<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use App\Component\GameLogger;
use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;

final class GameFactory
{
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger   = $logger;
    }
    
    public function createBackgammonNormalGame( bool $forGold ): Game
    {
        $game = new BackgammonNormalGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new Player();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new Player();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
        $game->PlayState = GameState::Created;
        
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        $game->LastDoubler = null;
        
        $game->Points = new ArrayCollection(); // 24 points, 1 bar and 1 home,
        
        for ( $i = 0; $i < 26; $i++ ) {
            $point  = new Point();
            $point->BlackNumber = $i;
            $point->WhiteNumber = 25 - $i;
            
            $game->Points[] = $point;
        }
        
        $game->Bars = new ArrayCollection();
        $game->Bars[PlayerColor::Black->value] = $game->Points->first();
        $game->Bars[PlayerColor::White->value] = $game->Points->last();
        
        $game->SetStartPosition();
        
        Game::CalcPointsLeft( $game );
        
        return $game;
    }
    
    public function createBackgammonGulBaraGame( bool $forGold ): Game
    {
        $game = new BackgammonGulBaraGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Points       = new ArrayCollection();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new Player();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new Player();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
        $game->PlayState = GameState::Created;
        
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        $game->LastDoubler = null;
        
        $game->Points = new ArrayCollection(); // 24 points, 1 bar and 1 home,
        
        for ( $i = 0; $i < 26; $i++ ) {
            $point  = new Point();
            $point->BlackNumber = $i;
            $point->WhiteNumber = 25 - $i;
            
            $game->Points[] = $point;
        }
        
        $game->Bars = new ArrayCollection();
        $game->Bars[PlayerColor::Black->value] = $game->Points->first();
        $game->Bars[PlayerColor::White->value] = $game->Points->last();
        
        $game->SetStartPosition();
        
        Game::CalcPointsLeft( $game );
        
        return $game;
    }
    
    public function createBackgammonTapaGame( bool $forGold ): Game
    {
        $game = new BackgammonTapaGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Points       = new ArrayCollection();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new Player();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new Player();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
        $game->PlayState = GameState::Created;
        
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        $game->LastDoubler = null;
        
        $game->Points = new ArrayCollection(); // 24 points, 1 bar and 1 home,
        
        for ( $i = 0; $i < 26; $i++ ) {
            $point  = new Point();
            $point->BlackNumber = $i;
            $point->WhiteNumber = 25 - $i;
            
            $game->Points[] = $point;
        }
        
        $game->Bars = new ArrayCollection();
        $game->Bars[PlayerColor::Black->value] = $game->Points->first();
        $game->Bars[PlayerColor::White->value] = $game->Points->last();
        
        $game->SetStartPosition();
        
        Game::CalcPointsLeft( $game );
        
        return $game;
    }
}
