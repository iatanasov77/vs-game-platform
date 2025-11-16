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
    
    /** @var ChessPiece */
    public $Piece;
}
