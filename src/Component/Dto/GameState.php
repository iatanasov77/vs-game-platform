<?php namespace App\Component\Dto;

enum GameState
{
    case opponentConnectWaiting;
    case firstThrow;
    case playing;
    case ended;
}
