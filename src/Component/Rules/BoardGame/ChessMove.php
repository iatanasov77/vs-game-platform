<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerColor;
use App\Component\Type\ChessMoveType;

class ChessMove
{
    /** @var PlayerColor */
    public $Color;
    
    /** @var ChessSquare */
    public $From;
    
    /** @var ChessSquare */
    public $To;
    
    /** @var ChessPiece */
    public $Piece;
    
    /** @var ChessPiece */
    public $CapturedPiece;	// Piece captured by this mov
    
    /** @var ChessPiece */
    public $PromoPiece;		// Piece selected after pawn promotion
    
    /** @var ChessPiece */
    public $EnPassantPiece;	// Piece captured during enpassant move
    
    /** @var ChessMoveType */
    public $Type;		// Type of the move
    
    /** @var bool */
    public $CauseCheck;		// if cause or leave the user under check
    
    /** @var int */
    public $Score;			// Score of the move from the board analyze routine
    
    /** @var Collection | ChessMove[] */
    public $NextMoves;
    
    public function __construct()
    {
        $this->NextMoves    = new ArrayCollection();
    }
    
    // Return true if the move was promo move
    public function IsPromoMove(): bool
    {
        return $this->Type == ChessMoveType::PromotionMove;
    }
    
    // Return true if the move was capture move
    public function IsCaptureMove(): bool
    {
        return $this->Type == ChessMoveType::CaputreMove;
    }
    
    //Return a descriptive move text
    public function __toString(): string
    {
        return "{$this->Color} {$this->From->GetNumber( $this->Color )} -> {$this->To->GetNumber( $this->Color )}";
        
        if ( $this->Type == ChessMoveType::CaputreMove ) {	// It's a capture move
            return "{$this->Piece} {$this->From->ToString2()}x{$this->To->ToString2()}";
        } else {
            return "{$this->Piece} {$this->From->ToString2()}-{$this->To->ToString2()}";
        }
    }
}
