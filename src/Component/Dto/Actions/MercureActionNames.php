<?php namespace App\Component\Dto\Actions;

enum MercureActionNames: int
{
    case testMessage        = 0;
    
    case gameLobbyCreated   = 1;
    case createGameLobby    = 2;
    case joinGameLobby      = 3;
    case leaveGameLobby     = 4;
    
    case disconnectPlayer   = 5;
}
