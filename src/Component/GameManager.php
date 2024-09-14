<?php namespace App\Component;

use Ratchet\Client as RC;


final class GameManager
{
    // Manual: https://github.com/ratchetphp/Pawl
    private function send()
    {
        RC\connect( 'wss://echo.websocket.org:443' )->then( function( $conn ) {
            $conn->on('message', function( $msg ) use ( $conn ) {
                echo "Received: {$msg}\n";
                $conn->close();
            });
                
            $conn->send( 'Hello World!' );
        }, function ( $e ) {
            echo "Could not connect: {$e->getMessage()}\n";
        });
    }
}