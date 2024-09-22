<?php namespace App\Component\Type;

enum GameState
{
    case OpponentConnectWaiting;
    case FirstThrow;
    case Playing;
    case Ended;
}
    