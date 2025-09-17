<?php namespace App\Component\Rules;

use Doctrine\Common\Collections\ArrayCollection;
use App\Component\GameLogger;
use App\Component\Utils\Guid;
use App\Component\GameVariant;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

// Games
use App\Component\Rules\BoardGame\BackgammonNormalGame;
use App\Component\Rules\BoardGame\BackgammonTapaGame;
use App\Component\Rules\BoardGame\BackgammonGulBaraGame;
use App\Component\Rules\CardGame\BridgeBeloteGame;

use App\Component\Rules\BoardGame\Player as BoardGamePlayer;
use App\Component\Rules\BoardGame\Point;

use App\Component\Rules\CardGame\Player as CardGamePlayer;
use App\Component\Rules\CardGame\Deck;

final class GameFactory
{
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger   = $logger;
    }
    
    public function createGame( string $gameCode, ?string $gameVariant, bool $ForGold ): GameInterface
    {
        switch ( $gameCode ) {
            case GameVariant::BACKGAMMON_CODE:
                return $this->createBackgammonGame( $gameVariant, $ForGold );
                break;
            case GameVariant::BRIDGE_BELOTE_CODE:
                return $this->createBridgeBeloteGame( $ForGold );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $this->Created          = new \DateTime( 'now' );
    }
    
    private function createBackgammonGame( string $gameVariant, bool $ForGold ): GameInterface
    {
        switch ( $gameVariant ) {
            case GameVariant::BACKGAMMON_NORMAL:
                return $this->createBackgammonNormalGame( $ForGold );
                break;
            case GameVariant::BACKGAMMON_TAPA:
                return $this->createBackgammonTapaGame( $ForGold );
                break;
            case GameVariant::BACKGAMMON_GULBARA:
                return $this->createBackgammonGulBaraGame( $ForGold );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Variant !!!' );
        }
    }
    
    private function createBackgammonNormalGame( bool $forGold ): GameInterface
    {
        $game = new BackgammonNormalGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new BoardGamePlayer();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new BoardGamePlayer();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
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
        
        BackgammonNormalGame::CalcPointsLeft( $game );
        
        return $game;
    }
    
    private function createBackgammonGulBaraGame( bool $forGold ): GameInterface
    {
        $game = new BackgammonGulBaraGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Points       = new ArrayCollection();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new BoardGamePlayer();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new BoardGamePlayer();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
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
        
        BackgammonGulBaraGame::CalcPointsLeft( $game );
        
        return $game;
    }
    
    private function createBackgammonTapaGame( bool $forGold ): GameInterface
    {
        $game = new BackgammonTapaGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->Points       = new ArrayCollection();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new BoardGamePlayer();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new BoardGamePlayer();
        $game->WhitePlayer->PlayerColor = PlayerColor::White;
        $game->WhitePlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
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
        
        BackgammonTapaGame::CalcPointsLeft( $game );
        
        return $game;
    }
    
    private function createBridgeBeloteGame( bool $forGold ): GameInterface
    {
        $game = new BridgeBeloteGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        
        $game->Players[PlayerPosition::South->value] = new CardGamePlayer();
        $game->Players[PlayerPosition::South->value]->PlayerPosition = PlayerPosition::South;
        $game->Players[PlayerPosition::South->value]->Name = "Guest";
        
        $game->Players[PlayerPosition::East->value] = new CardGamePlayer();
        $game->Players[PlayerPosition::East->value]->PlayerPosition = PlayerPosition::East;
        $game->Players[PlayerPosition::East->value]->Name = "Guest";
        
        $game->Players[PlayerPosition::North->value] = new CardGamePlayer();
        $game->Players[PlayerPosition::North->value]->PlayerPosition = PlayerPosition::North;
        $game->Players[PlayerPosition::North->value]->Name = "Guest";
        
        $game->Players[PlayerPosition::West->value] = new CardGamePlayer();
        $game->Players[PlayerPosition::West->value]->PlayerPosition = PlayerPosition::West;
        $game->Players[PlayerPosition::West->value]->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        
        $game->deck = new Deck();
        $game->pile = [];
        $game->teamsTricks = [[], []];
        
        $game->SetStartPosition();
        
        return $game;
    }
}
