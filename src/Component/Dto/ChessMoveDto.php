<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessMoveType;
use App\Component\Type\ChessPieceType;

class ChessMoveDto
{
    public PlayerColor $color;
    public ChessMoveType $type;
    public string $from;
    public string $to;
    
    public bool $causeCheck = false;
    
    /** @var ChessPieceType */
    public $piece;
    
    /** @var ChessPieceType | null */
    public $capturedPiece;
    
    /** @var ChessPieceType | null */
    public $promoPiece;
    
    /** @var ChessPieceType | null */
    public $enpassantPiece;
    
    public Collection $nextMoves; // ChessMoveDto[]
    public bool $animate    = false;
    public bool $hint       = false;
}
