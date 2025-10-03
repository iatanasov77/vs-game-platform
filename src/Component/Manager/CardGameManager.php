<?php namespace App\Component\Manager;

use Ratchet\RFC6455\Messaging\Frame;
use App\Component\Websocket\Client\WebsocketClientInterface;

// Types
use App\Component\Type\CardGameTeam;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Actions\BidMadeActionDto;

abstract class CardGameManager extends AbstractGameManager
{
    abstract protected function DoBid( BidMadeActionDto $action ): void;
    
    protected function SaveWinner( CardGameTeam $team ): ?array
    {
        
    }
    
    protected function GetWinner(): ?CardGameTeam
    {
        $winner = null;
        
        
        return $winner;
    }
    
    protected function SendWinner( CardGameTeam $team, ?array $newScore ): void
    {
        
    }
    
    protected function Resign( PlayerPosition $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( PlayerPosition $winner ): void
    {
        //$this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        //$newScore = $this->SaveWinner( $winner );
        //$this->SendWinner( $winner, $newScore );
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket ): void
    {
        if ( $socket != null ) {
            $this->logger->log( "Closing client", 'ExitGame' );
            $socket->close( Frame::CLOSE_NORMAL );
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerPosition::South->value ) ) {
                $this->Clients->set( PlayerPosition::South->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::North->value ) ) {
                $this->Clients->set( PlayerPosition::North->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::East->value ) ) {
                $this->Clients->set( PlayerPosition::East->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::West->value ) ) {
                $this->Clients->set( PlayerPosition::West->value, null );
            }
        }
    }
}
