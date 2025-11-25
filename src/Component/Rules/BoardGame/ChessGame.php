<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessPieceType;
use App\Component\Type\ChessMoveType;

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
    
    /** @var Collection | ChessPiece[] */
    public $CapturedPieces;
    
    /** @var Collection | ChessMove[] */
    public $MovesHistory;
    
    /** @var bool */
    public $DoPrincipleVariation;	// True when computer should use principle variation to optimize search
    
    /** @var bool */
    public $DoQuiescentSearch;		// Return true when computer should do Queiscent search
    
    /** @var bool */
    public $UnderCheck;
    
    /** @var PlayerColor */
    public $CauseCheckPlayer;
    
    public function __construct( GameLogger $logger )
    {
        parent::__construct( $logger );
        
        $this->CapturedPieces = new ArrayCollection();
        $this->Rules    = new ChessRules( $this, $logger );
        
        $this->UnderCheck = false;
        $this->CauseCheckPlayer = PlayerColor::Neither;
    }
    
    public function SetStartPosition(): void
    {
        // Now setup the board for black side
        $this->Squares["A8"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::Black ) );
        $this->Squares["H8"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::Black ) );
        $this->Squares["B8"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::Black ) );
        $this->Squares["G8"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::Black ) );
        $this->Squares["C8"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::Black ) );
        $this->Squares["F8"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::Black ) );
        $this->Squares["E8"]->Piece = new ChessPiece( ChessPieceType::King, new ChessSide( PlayerColor::Black ) );
        $this->Squares["D8"]->Piece = new ChessPiece( ChessPieceType::Queen, new ChessSide( PlayerColor::Black ) );
		for ( $col = 1; $col <= 8; $col++ ) {
		    $chrCol = chr( $col + 64 );
		    $key = "{$chrCol}7";
		    $this->Squares[$key]->Piece = new ChessPiece( ChessPieceType::Pawn, new ChessSide( PlayerColor::Black ) );
		}

		// Now setup the board for white side
		$this->Squares["A1"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::White ) );
		$this->Squares["H1"]->Piece = new ChessPiece( ChessPieceType::Rook, new ChessSide( PlayerColor::White ) );
		$this->Squares["B1"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::White ) );
		$this->Squares["G1"]->Piece = new ChessPiece( ChessPieceType::Knight, new ChessSide( PlayerColor::White ) );
		$this->Squares["C1"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::White ) );
		$this->Squares["F1"]->Piece = new ChessPiece( ChessPieceType::Bishop, new ChessSide( PlayerColor::White ) );
		$this->Squares["E1"]->Piece = new ChessPiece( ChessPieceType::King, new ChessSide( PlayerColor::White ) );
		$this->Squares["D1"]->Piece = new ChessPiece( ChessPieceType::Queen, new ChessSide( PlayerColor::White ) );
		for ( $col=1; $col <= 8; $col++ ) {
		    $chrCol = chr( $col + 64 );
		    $key = "{$chrCol}2";
		    $this->Squares[$key]->Piece = new ChessPiece( ChessPieceType::Pawn, new ChessSide( PlayerColor::White ) );
		}
		
		//$this->logger->log( "Squares: " . print_r( $this->Squares->toArray(), true ), 'GameManager' );
    }
    
    public function MakeMove( ChessMove &$move ): ?ChessPiece
    {
        //$this->logger->log( "MakeMove: " . print_r( $move, true ), 'GenerateMoves' );
        
        if ( $this->Squares["{$move->To}"]->Piece ) {
            $this->CapturedPieces[] = $this->Squares["{$move->To}"]->Piece;
            $move->Type = ChessMoveType::CaputreMove;
            $move->CapturedPiece = $this->Squares["{$move->To}"]->Piece;
        }
        
        $movedPiece = $this->Squares["{$move->From}"]->Piece;
        $movedPiece->Moves++;
        $this->Squares["{$move->From}"]->Piece = null;
        $this->Squares["{$move->To}"]->Piece = $movedPiece;
        
        $move->Piece = $movedPiece;
        $this->MovesHistory[] = $move;
        
        $this->UnderCheck = $move->CauseCheck;
        $this->CauseCheckPlayer = $this->UnderCheck ? $move->Color : PlayerColor::Neither;
        
        return $move->CapturedPiece ?: null;
    }
    
    public function UndoMove( ChessMove &$move, ?ChessPiece $capturedPiece ): void
    {
        $movedPiece = $this->Squares["{$move->To}"]->Piece;
        $this->Squares["{$move->To}"]->Piece = null;
        $this->Squares["{$move->From}"]->Piece = $movedPiece;
        
        if ( $capturedPiece != null ) {
            $this->Squares["{$move->To}"]->Piece = $capturedPiece;
        }
    }
    
    // get all the cell containg pieces of given side
    public function GetSideCell( PlayerColor $PlayerSide, Collection $allSquares ): Collection
    {
        $CellNames = new ArrayCollection();
        
        // Loop all the squars and store them in Array List
        for ( $row = 1; $row <= 8; $row++ ) {
            for ( $col = 1; $col <= 8; $col++ ) {
                $chrCol = chr( $col + 64 );
                $key = "{$chrCol}{$row}";
                
                // check and add the current type cell
                if (
                    $allSquares[$key]->Piece != null &&
                    $allSquares[$key]->Piece->Side->type == $PlayerSide
                ) {
                    $CellNames[] = "{$allSquares[$key]}"; // append the cell name to list
                }
            }
        }
        
        return $CellNames;
    }
    
    public function SetFirstMoveWinner(): void
    {
        if ( $this->PlayState == GameState::firstMove ) {
            // Always White moves first, then players alternate moves.
            $this->CurrentPlayer = PlayerColor::White;
            
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
