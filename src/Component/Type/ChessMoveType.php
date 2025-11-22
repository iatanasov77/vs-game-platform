<?php namespace App\Component\Type;

enum ChessMoveType: int
{
    case NormalMove     = 0;
    case CaputreMove    = 1;
    case TowerMove      = 2;
    case PromotionMove  = 3;
    case EnPassant      = 4;
}
