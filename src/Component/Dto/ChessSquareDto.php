<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\Collection;

class ChessSquareDto
{
    public int $Rank;
    public string $File;
    public ?ChessPieceDto $Piece;
}
