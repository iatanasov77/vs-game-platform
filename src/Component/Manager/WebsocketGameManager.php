<?php namespace App\Component\Manager;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Type\PlayerColor;
use App\Component\AI\Backgammon\EngineFactory as AiEngineFactory;
use App\Entity\GamePlayer;
use App\Component\System\Guid;

final class WebsocketGameManager extends AbstractGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, PlayerColor $color, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        $this->Game->CurrentPlayer  = $color;
        
        if ( $color == PlayerColor::Black ) {
            $this->Client1 = $webSocket;
            
            
            $this->Game->BlackPlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->BlackPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->BlackPlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->BlackPlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->BlackPlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            
            if ( $this->Game->IsGoldGame ) {
                $this->Game->BlackPlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
                $this->Game->Stake = self::firstBet * 2;
            }
            
            if ( $playAi ) {
                $this->logger->log( "Play AI is TRUE !!!", 'GameManager' );
                
                $aiUser = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
                
                $this->Game->WhitePlayer->Id = $aiUser->getId();
                $this->Game->WhitePlayer->Guid = $aiUser->getGuid();
                $this->Game->WhitePlayer->Name = $aiUser->getName();
                /** @TODO: AI image */
                $this->Game->WhitePlayer->Photo = $aiUser->getPhotoUrl();
                $this->Game->WhitePlayer->Elo = $aiUser->getElo();
                
                if ( $this->Game->IsGoldGame ) {
                    $this->Game->WhitePlayer->Gold = $aiUser->getGold();
                }
                
                $this->Engine = AiEngineFactory::CreateBackgammonEngine(
                    $this->GameCode,
                    $this->GameVariant,
                    $this->logger,
                    $this->Game
                );
                $this->CreateDbGame();
                $this->StartGame();
                
                if ( $this->Game->CurrentPlayer == PlayerColor::White ) {
                    $promise = \React\Async\async( function () {
                        $this->logger->log( "GameManager CurrentPlayer: White", 'GameManager' );
                        $this->EnginMoves( $this->Client1 );
                    })();
                    \React\Async\await( $promise );
                }
            }
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as white. This is not expected" );
            }
            $this->Client2 = $webSocket;
            
            $this->Game->WhitePlayer->Id = $dbUser != null ? $dbUser->getId() : 0;
            $this->Game->BlackPlayer->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
            $this->Game->WhitePlayer->Name = $dbUser != null ? $dbUser->getName() : "Guest";
            $this->Game->WhitePlayer->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
            $this->Game->WhitePlayer->Elo = $dbUser != null ? $dbUser->getElo() : 0;
            
            if ( $this->Game->IsGoldGame ) {
                $this->Game->WhitePlayer->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
            }
            $this->CreateDbGame();
            $this->StartGame();
            
            //$this->dispatchGameEnded();
        }
    }
}
