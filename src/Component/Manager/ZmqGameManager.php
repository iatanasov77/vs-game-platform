<?php namespace App\Component\Manager;

use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

use App\EventListener\GameEndedEvent;
use App\Component\System\Guid;
use App\Component\Rules\Backgammon\Game;
use App\Component\Ai\Backgammon\Engine as AiEngine;
use App\Component\Dto\Mapper;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Websocket\WebSocketState;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// Actions
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\DicesRolledActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\DoublingActionDto;

use App\Entity\GamePlayer;

class ZmqGameManager extends AbstractGameManager
{
    public function Send( WebsocketClientInterface $socket, object $obj ): void
    {
        if ( $socket == null || $socket->State != WebSocketState::Open ) {
            $this->logger->info( "Cannot send to socket, connection was lost" );
            return;
        }
        
        $socketObject = new \stdClass();
        $socketObject->topic    = 'game';
        $socketObject->data     = $obj;
        
        $json = \json_encode( $obj );
        $this->logger->info( "Sending to client {$json}" );
        
        try {
            $socket->send( $socketObject );
        } catch ( \Exception $exc ) {
            $this->logger->error( "Failed to send socket data. Exception: {$exc->getMessage()}" );
        }
    }
    
    public function ConnectAndListen( WebsocketClientInterface $webSocket, PlayerColor $color, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->Game     = Game::Create( true );
        $this->Created  = new \DateTime( 'now' );
        
        if ( $color == PlayerColor::Black ) {
            $this->Client1 = $webSocket;
            
            $this->Game->CurrentPlayer  = PlayerColor::Black;
            $this->Game->BlackPlayer->Id = $dbUser != null ? $dbUser->getId() : Guid::Empty();
            $this->Game->BlackPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->BlackPlayer->PlayerColor = PlayerColor::Black;
            $this->Game->BlackPlayer->Photo = $dbUser != null && false ? $dbUser->getPhotoUrl() : "";
            $this->Game->BlackPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->BlackPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                $this->Game->WhitePlayer->Id = $aiUser->getId();
                $this->Game->WhitePlayer->Name = $aiUser->getName();
                $this->Game->WhitePlayer->PlayerColor = PlayerColor::White;
                // TODO: AI image
                $this->Game->WhitePlayer->Photo = "";
                $this->Game->WhitePlayer->Elo = $aiUser->getElo();
                if ( $this->Game->IsGoldGame ) {
                    $this->Game->WhitePlayer->Gold = $aiUser->getGold();
                }
                
                $this->Engine = new AiEngine( $this->Game );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::White ) {
                    //async( \Closure::fromCallable( [$this, 'EnginMoves'] ), [$this->Client1] )->await();
                }
            }
                
            $this->ListenOn( $webSocket );
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            
            $this->Client2 = $webSocket;
            
            $this->Game->CurrentPlayer  = PlayerColor::White;
            $this->Game->WhitePlayer->Id = $dbUser != null ? $dbUser->Id : Guid::Empty();
            $this->Game->WhitePlayer->Name = $dbUser != null ? $dbUser->Name : "Guest";
            $this->Game->WhitePlayer->Photo = $dbUser != null && $dbUser->ShowPhoto ? $dbUser->PhotoUrl : "";
            $this->Game->WhitePlayer->Elo = $dbUser != null ? $dbUser->Elo : 0;
            if ( $this->Game->IsGoldGame ) {
                $this->Game->WhitePlayer->Gold = $dbUser != null ? $dbUser->Gold - self::firstBet : 0;
            }
                
            $this->CreateDbGame();
            $this->StartGame();
            
            $this->ListenOn( $webSocket );
        }
    }
    
    private function ListenOn( WebsocketClientInterface $socket ): void
    {
        while (
            $socket->State != WebSocketState::Closed &&
            $socket->State != WebSocketState::Aborted &&
            $socket->State != WebSocketState::CloseReceived
        ) {
            $text = $this->ReceiveText( $socket );
            if ( $text != null && ! empty( $text ) ) {
                $this->logger->info( "Received: {$text}" );
            
                try {
                    $action = \json_decode( $text );
                    $otherClient = $socket == $this->Client1 ? $this->Client2 : $this->Client1;
                    
                    // PHP Way to Call Async Methods
                    async( \Closure::fromCallable( [$this, 'DoAction'] ), [
                        $action->actionName,
                        $text,
                        $socket,
                        $otherClient
                    ])->await();
                } catch ( \Exception $e ) {
                    $this->logger->error( "Failed to parse Action text {$e->getMessage()}" );
                }
            }
        }
    }
}