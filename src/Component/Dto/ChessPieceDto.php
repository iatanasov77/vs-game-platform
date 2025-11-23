<?php namespace App\Component\Dto;

use App\Component\Type\ChessPieceType;
use App\Component\Type\PlayerColor;

class ChessPieceDto
{
    /** @var ChessPieceType */
    public $Type;
    
    /** @var PlayerColor */
    public $Side;
    
    /** @var int */
    public $Moves;
}
