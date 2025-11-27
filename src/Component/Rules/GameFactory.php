<?php namespace App\Component\Rules;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
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
use App\Component\Rules\BoardGame\ChessGame;
use App\Component\Rules\CardGame\BridgeBeloteGame;
use App\Component\Rules\CardGame\ContractBridgeGame;

use App\Component\Rules\BoardGame\Player as BoardGamePlayer;
use App\Component\Rules\BoardGame\Point;
use App\Component\Rules\BoardGame\ChessSquare;

use App\Component\Rules\CardGame\Player as CardGamePlayer;
use App\Component\Rules\CardGame\Deck;

final class GameFactory
{
    /** @var GameLogger */
    private  $logger;
    
    /** @var EventDispatcherInterface */
    private $eventDispatcher;
    
    public function __construct( GameLogger $logger, EventDispatcherInterface $eventDispatcher )
    {
        $this->logger           = $logger;
        $this->eventDispatcher  = $eventDispatcher;
    }
    
    public function createGame( string $gameCode, ?string $gameVariant, bool $ForGold ): GameInterface
    {
        switch ( $gameCode ) {
            case GameVariant::BACKGAMMON_CODE:
                return $this->createBackgammonGame( $gameCode, $gameVariant, $ForGold );
                break;
            case GameVariant::CHESS_CODE:
                return $this->createChessGame( $gameCode, $ForGold );
                break;
            case GameVariant::BRIDGE_BELOTE_CODE:
                return $this->createBridgeBeloteGame( $gameCode, $ForGold );
                break;
            case GameVariant::CONTRACT_BRIDGE_CODE:
                return $this->createContractBridgeGame( $gameCode, $ForGold );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Code !!!' );
        }
        
        $this->Game->ThinkStart = new \DateTime( 'now' );
        $this->Created          = new \DateTime( 'now' );
    }
    
    private function createBackgammonGame( string $gameCode, string $gameVariant, bool $ForGold ): GameInterface
    {
        switch ( $gameVariant ) {
            case GameVariant::BACKGAMMON_NORMAL:
                return $this->createBackgammonNormalGame( $gameCode, $ForGold );
                break;
            case GameVariant::BACKGAMMON_TAPA:
                return $this->createBackgammonTapaGame( $gameCode, $ForGold );
                break;
            case GameVariant::BACKGAMMON_GULBARA:
                return $this->createBackgammonGulBaraGame( $gameCode, $ForGold );
                break;
            default:
                throw new \RuntimeException( 'Unknown Game Variant !!!' );
        }
    }
    
    private function createBackgammonNormalGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new BackgammonNormalGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
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
    
    private function createBackgammonGulBaraGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new BackgammonGulBaraGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
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
    
    private function createBackgammonTapaGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new BackgammonTapaGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
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
    
    private function createChessGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new ChessGame( $this->logger );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
        //$game->ValidMoves   = new ArrayCollection();
        
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
        
        $game->Squares = new ArrayCollection(); // Board divided into a grid of 64 squares (eight-by-eight) of alternating color
        for ( $row = 1; $row <= 8; $row++ ) {
            for ( $col = 1; $col <= 8; $col++ ) {
                $square = new ChessSquare();
                $square->Rank = $row;
                $square->File = chr( $col + 64 );
                
                $game->Squares->set( "{$square->File}{$square->Rank}", $square ); // Initialize and add the new chess cell
            }
        }
        
        $game->MovesHistory = new ArrayCollection();
        
        $game->SetStartPosition();
        
        //BackgammonNormalGame::CalcPointsLeft( $game );
        
        return $game;
    }
    
    private function createBridgeBeloteGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new BridgeBeloteGame( $this->logger, $this->eventDispatcher );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
        
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
        
        $game->Deck = new Deck();
        $game->Pile = new ArrayCollection();
        $game->SouthNorthTricks = new ArrayCollection();
        $game->EastWestTricks = new ArrayCollection();
        
        $game->AvailableBids = new ArrayCollection();
        $game->ValidCards = new ArrayCollection();
        $game->Bids = new ArrayCollection();
        
        $game->SetStartPosition();
        
        return $game;
    }
    
    private function createContractBridgeGame( string $gameCode, bool $forGold ): GameInterface
    {
        $game = new ContractBridgeGame( $this->logger, $this->eventDispatcher );
        
        $game->Id           = Guid::NewGuid();
        $game->GameCode     = $gameCode;
        
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
        
        $game->Deck = new Deck();
        $game->Pile = new ArrayCollection();
        $game->SouthNorthTricks = new ArrayCollection();
        $game->EastWestTricks = new ArrayCollection();
        
        $game->AvailableBids = new ArrayCollection();
        $game->ValidCards = new ArrayCollection();
        $game->Bids = new ArrayCollection();
        
        $game->SetStartPosition();
        
        return $game;
    }
}
