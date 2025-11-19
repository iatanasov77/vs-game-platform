<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessPieceType;

/**
 * Using 'ngx-chess-board': https://github.com/marwan-mohamed12/Pencil-chess-game
 * ChessEngine in C#: https://www.syedgakbar.com/projects/chess
 */
class ChessGame extends Game
{
    /** @var ChessRules */
    public $Rules;
    
    /** @var Collection | ChessSquare[] */
    public $Squares;
    
    /** @var Collection | ChessMove[] */
    public $MovesHistory;
    
    /** @var bool */
    public $DoPrincipleVariation;	// True when computer should use principle variation to optimize search
    
    /** @var bool */
    public $DoQuiescentSearch;		// Return true when computer should do Queiscent search
    
    public function __construct( GameLogger $logger )
    {
        parent::__construct( $logger );
        
        $this->Rules    = new ChessRules( $this, $logger );
    }
    
    public function SetStartPosition(): void
    {
        // Now setup the board for black side
        $this->Squares["A1"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::Black ) );
        $this->Squares["H1"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::Black ) );
        $this->Squares["B1"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::Black ) );
        $this->Squares["G1"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::Black ) );
        $this->Squares["C1"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::Black ) );
        $this->Squares["F1"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::Black ) );
        $this->Squares["E1"]->Piece = new ChessPiece( ChessPieceType::King, new ChessSide( PlayerColor::Black ) );
        $this->Squares["D1"]->Piece = new ChessPiece( ChessPieceType::Queen, new ChessSide( PlayerColor::Black ) );
		for ( $col = 1; $col <= 8; $col++ ) {
		    $chrCol = chr( $col + 64 );
		    $key = "{$chrCol}2";
		    $this->Squares[$key]->Piece = new ChessPiece( ChessPieceType::Pawn, new ChessSide( PlayerColor::Black ) );
		}

		// Now setup the board for white side
		$this->Squares["A8"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::White ) );
		$this->Squares["H8"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::White ) );
		$this->Squares["B8"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::White ) );
		$this->Squares["G8"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::White ) );
		$this->Squares["C8"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::White ) );
		$this->Squares["F8"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::White ) );
		$this->Squares["E8"]->Piece = new ChessPiece( ChessPieceType::King, new ChessSide( PlayerColor::White ) );
		$this->Squares["D8"]->Piece = new ChessPiece( ChessPieceType::Queen, new ChessSide( PlayerColor::White ) );
		for ( $col=1; $col <= 8; $col++ ) {
		    $chrCol = chr( $col + 64 );
		    $key = "{$chrCol}7";
		    $this->Squares[$key]->Piece = new ChessPiece( ChessPieceType::Pawn, new ChessSide( PlayerColor::White ) );
		}
		
		//$this->logger->log( "Squares: " . print_r( $this->Squares->toArray(), true ), 'GameManager' );
    }
    
    // get all the cell containg pieces of given side
    public function GetSideCell( PlayerColor $PlayerSide ): Collection
    {
        $CellNames = new ArrayCollection();
        
        // Loop all the squars and store them in Array List
        for ( $row = 1; $row <= 8; $row++ ) {
            for ( $col = 1; $col <= 8; $col++ ) {
                $chrCol = chr( $col + 64 );
                $key = "{$chrCol}{$row}";
                
                // check and add the current type cell
                if (
                    $this->Squares[$key]->Piece != null &&
                    $this->Squares[$key]->Piece->Color == $PlayerSide
                ) {
                    $CellNames[] = "{$this->Squares[$key]}"; // append the cell name to list
                }
            }
        }
        
        return $CellNames;
    }
    
    public function SetFirstMoveWinner(): void
    {
        if ( $this->PlayState == GameState::firstMove ) {
            $this->CurrentPlayer = PlayerColor::Black;
            $this->PlayState = GameState::playing;
        }
    }
    
    public function FakeMove( int $v1, int $v2 ): void
    {
        $this->SetFirstMWinner();
    }
    
    public function StartGame(): void
    {
        $this->SetFirstMoveWinner();
        
        // $this->logger->log( 'CurrentPlayer: ' . $this->CurrentPlayer->value, 'FirstThrowState' );
        /*
        $this->ClearMoves( $this->ValidMoves );
        $this->_GenerateMoves( $this->ValidMoves );
        */
        
        Game::$DebugValidMoves++;
        //$this->logger->debug( $this->ValidMoves, 'ValidMoves_' . Game::$DebugValidMoves .  '.txt' );
    }
    
    public function GenerateMoves(): array
    {
        $moves = new ArrayCollection();
        $this->_GenerateMoves( $moves );
    }
    
    protected function _GenerateMoves( Collection &$moves ): void
    {
        $currentPlayer  = $this->CurrentPlayer;
    }
}
