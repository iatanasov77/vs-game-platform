<?php namespace App\Component\Rules\BoardGame;

class ChessSquare
{
    /**
     * Horizontal rows are called ranks
     * In algebraic notation, ranks are numbered 1–8
     * 
     * @var int
     */
    public $Rank;
    
    /**
     * Vertical columns are called files
     * In algebraic notation, files are named either using its position a–h
     *
     * @var string
     */
    public $File;
    
    /** @var ChessPiece | null */
    public $Piece = null;
    
    // returns true if the cell is owned by enemy of the given cell
    public function IsOwnedByEnemy( ChessSquare $other ): bool
    {
        if ( ! $this->Piece ) {
            return false;
        } else {
            return $this->Piece->Side->type != $other->Piece->Side->type;
        }
    }
    
    // returns true if the current cell is owned by source cell
    public function IsOwned( ChessSquare $other ): bool
    {
        if ( ! $this->Piece ) {
            return false;
        } else {
            return $this->Piece->Side->type == $other->Piece->Side->type;
        }
    }
    
    // Return chess friendly location string from the internal row and column integers
    public function __toString(): string
    {
        return "{$this->File}{$this->Rank}";
    }
    
    // Return chess friendly location for UI Interface
    public function ToString2(): string
    {
        $strLoc="";
        $BoardRow = \abs( 8 - $this->Rank ) + 1;		// On the chess board column start from bottom
        $strLoc = "{$this->File}{$BoardRow}";	// Convert the row literal i.e. 1=a, 2=b and so on.
        
        return $strLoc;	// return back the converted string
    }
}
