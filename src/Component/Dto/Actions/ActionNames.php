<?php namespace App\Component\Dto\Actions;

enum ActionNames
{
    case gameCreated;
    case dicesRolled;
    case movesMade;
    case gameEnded;
    case opponentMove;
    case undoMove;
    case connectionInfo;
    case gameRestore;
    case resign;
    case createGame;
    case exitGame;
    case requestedDoubling;
    case acceptedDoubling;
    case rolled;
    case requestHint;
    case hintMoves;
}
