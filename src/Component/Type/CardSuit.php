<?php namespace App\Component\Type;

enum CardSuit: int
{
    case Club       = 0;
    case Diamond    = 1;
    case Heart      = 2;
    case Spade      = 3;
    
    public function toString(): string
    {
        return match( $this ) {
            CardSuit::Club => "\u2663", // ♣
            CardSuit::Diamond => "\u2666", // ♦
            CardSuit::Heart => "\u2665", // ♥
            CardSuit::Spade => "\u2660", // ♠
        };
    }
}
