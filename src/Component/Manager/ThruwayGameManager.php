<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;
use App\Component\Rules\Backgammon\AiEngine;
use App\Entity\GamePlayer;

final class ThruwayGameManager extends AbstractGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, PlayerColor $color, GamePlayer $dbUser, bool $playAi ): void
    {
        if ( $color == PlayerColor::Black ) {
            $this->Client1 = $webSocket;
            
            $this->Game->BlackPlayer->Id = $dbUser != null ? $dbUser->getId() : Guid::Empty();
            $this->Game->BlackPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->BlackPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $dbUser->getPhotoUrl() : "";
            $this->Game->BlackPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            
            if ( $this->Game->IsGoldGame ) {
                $this->Game->BlackPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                
                $this->Game->WhitePlayer->Id = $aiUser->getId();
                $this->Game->WhitePlayer->Name = $aiUser->getName();
                /** @TODO: AI image */
                $this->Game->WhitePlayer->Photo = $aiUser->getPhotoUrl();
                $this->Game->WhitePlayer->Elo = $aiUser->getElo();
                
                if ( $this->Game->IsGoldGame ) {
                    $this->Game->WhitePlayer->Gold = $aiUser->getGold();
                }
                
                $this->Engine = new AiEngine( $this->Game );
                
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::White ) {
                    $this->EnginMoves( $this->Client1 );
                }
            }
            
            $this->ListenOn( $webSocket );
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            $this->Client2 = $webSocket;
            
            $this->Game->WhitePlayer->Id = $dbUser != null ? $dbUser->getId() : Guid::Empty();
            $this->Game->WhitePlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->WhitePlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $dbUser->getPhotoUrl() : "";
            $this->Game->WhitePlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            
            if ( $this->Game->IsGoldGame ) {
                $this->Game->WhitePlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
            }
            
            $this->CreateDbGame();
            $this->StartGame();
            
            $this->ListenOn( $webSocket );
        }
    }
    
    private function ListenOn( WebsocketClientInterface $socket ): void
    {
        $socket->subscribe( "realm1", "game", \Closure::fromCallable( [$this, 'DoAction'] ) );
    }
}
