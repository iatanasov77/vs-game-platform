<?php namespace App\Component\Type;

enum ChessPieceType: int
{
    case King   = 0;
    case Queen  = 1;
    case Rook   = 2;
    case Bishop = 3;
    case Knight = 4;
    case Pawn   = 5;					
}