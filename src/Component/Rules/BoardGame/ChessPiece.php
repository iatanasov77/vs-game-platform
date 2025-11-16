<?php namespace App\Component\Rules\BoardGame;

use App\Component\Type\PlayerColor;
use App\Component\Type\ChessPieceType;

class ChessPiece
{
    /** @var PlayerColor */
    public $Color;
    
    /** @var ChessPieceType */
    public $Piece;
}
