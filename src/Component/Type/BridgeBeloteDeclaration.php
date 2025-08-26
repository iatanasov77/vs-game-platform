<?php namespace App\Component\Type;

enum BridgeBeloteDeclaration: int
{
    /**
     * a sequence of three (sequences are in the "A K Q J 10 9 8 7" order of the same suit) – is worth 20 points.
     */
    case tierce     = 0;
    
    /**
     * a sequence of four – is worth 50 points.
     */
    case quarte     = 1;
    
    /**
     * a sequence of five – is worth 100 points
     * (longer sequences are not awarded, a sequence of eight is counted as a quinte plus a tierce).
     */
    case quinte     = 2;
    
    /**
     * A carré – 4 of the same rank – of Jacks is worth 200 points.
     */
    case carréJacks = 3;
    
    /**
     * A carré of nines is worth 150 points.
     */
    case carréNines = 4;
    
    /**
     * A carré of aces, kings, queens, or tens is worth 100 points (sevens and eights are not awarded).
     */
    case carréTens  = 5;
}
