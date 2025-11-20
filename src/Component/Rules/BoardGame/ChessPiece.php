<?php namespace App\Component\Rules\BoardGame;

use App\Component\Type\ChessPieceType;

class ChessPiece
{
    /**
     * total no. of moves by the piece
     * 
     * @var int
     */
    public $Moves = 0;
    
    /** @var ChessSide */
    public $Side;
    
    /** @var ChessPieceType */
    public $Type;
    
    public function __construct( ChessPieceType $type, ChessSide $side )
    {
        $this->Side = $side;
        $this->Type = $type;
    }
    
    // Returns back weight of the chess peice
    public function GetWeight(): int
    {
        switch ( $this->Type ) {
            case ChessPieceType::King:
                return 0;
            case ChessPieceType::Queen:
                return 900;
            case ChessPieceType::Rook:
                return 500;
            case ChessPieceType::Bishop:
                return 325;
            case ChessPieceType::Knight:
                return 300;
            case ChessPieceType::Pawn:
                return 100;
            default:
                return 0;
        }
    }
    
    public function __toString(): string
    {
        switch ( $this->type )
        {
            case ChessPieceType::King:
                return "King";
            case ChessPieceType::Queen:
                return "Queen";
            case ChessPieceType::Bishop:
                return "Bishop";
            case ChessPieceType::Rook:
                return "Rook";
            case ChessPieceType::Knight:
                return "Knight";
            case ChessPieceType::Pawn:
                return "Pawn";
            default:
                return "E";
        }
    }
}
