<?php namespace App\Component\Manager;

use Doctrine\DBAL\Exception\DriverException;

use Vankosoft\WebsocketBundle\Websocket\WebSocketState;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketClientInterface;
use Vankosoft\WebsocketBundle\Websocket\Client\WebsocketSwooleClient;
use Vankosoft\WebsocketBundle\Websocket\Message\Frame;

use App\Component\Rules\BoardGame\Score;

// Types
use App\Component\GamePlatform;
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Utils\Guid;

use App\Entity\GamePlayer;
use App\Entity\TempPlayer;
use App\Component\Rules\BoardGame\Player;

abstract class BoardGameManager extends AbstractGameManager
{
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        $myColor = PlayerColor::from( $playerPositionId );
        
        $gameDto = Mapper::BoardGameToDto( $this->Game );
        $restoreAction = new GameRestoreActionDto();
        $restoreAction->game = $gameDto;
        $restoreAction->myColor = $myColor;
        
        // @TODO May be i can check if the game have Dices.
        $restoreAction->dices = $this->Game->Roll->map(
            function( $entry ) {
                return Mapper::DiceToDto( $entry );
            }
        )->toArray();
        
        if ( $myColor == PlayerColor::Black ) {
            $this->Clients->set( PlayerColor::Black->value, $socket );
            $otherSocket = $this->Clients->get( PlayerColor::White->value );
        } else {
            $this->Clients->set( PlayerColor::White->value, $socket );
            $otherSocket = $this->Clients->get( PlayerColor::Black->value );
        }
        
        $this->Send( $socket, $restoreAction );
        //Also send the state to the other client in case it has made moves.
        if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
            $restoreAction->myColor = $myColor == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
            $this->Send( $otherSocket, $restoreAction );
        } else {
            $this->logger->log( "Failed to send restore to other client", 'GameManager' );
        }
    }
    
    protected function ConnectAiOpponent( WebsocketClientInterface $socket ): void
    {
        $this->logger->log( "GameManager CurrentPlayer: White", 'GameManager' );
        $this->EnginMoves( $this->Clients->get( PlayerColor::Black->value ) );
    }
    
    protected function TimeTick(): void
    {
        if ( ! $this->moveTimeOut->isCancelled() ) {
            $ellapsed = ( new \DateTime( 'now' ) )->getTimestamp() - $this->Game->ThinkStart->getTimestamp();
            if ( $ellapsed > Game::TotalThinkTime ) {
                $CurrentPlayerColor = $this->Game->CurrentPlayer == PlayerColor::Black ? 'Black' : 'White';
                $this->logger->log( "The time run out for {$CurrentPlayerColor}", 'GameManager' );
                
                $this->moveTimeOut->cancel();
                $winner = $this->Game->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
                $this->EndGame( $winner );
            }
        }
    }
    
    protected function CreateDbGame(): void
    {
        try {
            $blackPlayer = $this->playersRepository->find( $this->Game->Players[PlayerColor::Black->value]->Id );
            $whitePlayer = $this->playersRepository->find( $this->Game->Players[PlayerColor::White->value]->Id );
            
            $tempBlackPlayer = $this->CreateTempPlayer( $blackPlayer, PlayerColor::Black );
            $tempWhitePlayer = $this->CreateTempPlayer( $whitePlayer, PlayerColor::White );
            
            $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
            $game       = $this->gamePlayFactory->createNew();
            $game->setGame( $gameBase );
            $game->setOwner( $blackPlayer );
            $game->setGuid( $this->Game->Id );
            
            $tempBlackPlayer->setGame( $game );
            $tempWhitePlayer->setGame( $game );
            
            $game->addGamePlayer( $tempBlackPlayer );
            $game->addGamePlayer( $tempWhitePlayer );
            
            $em = $this->doctrine->getManager();
            $em->persist( $game );
            $em->flush();
        } catch ( DriverException $e ) {
            $this->logger->log( "Has CreateDbGame Mysql Exception !!!", 'GameManager' );
            
            return;
        }
    }
    
    protected function AisTurn(): bool
    {
        $plyr = $this->Game->CurrentPlayer == PlayerColor::Black ? $this->Game->Players[PlayerColor::Black->value] : $this->Game->Players[PlayerColor::White->value];
        
        return $plyr->IsAi();
    }
    
    protected function SaveWinner( PlayerColor $color ): ?array
    {
        if ( ! $this->Game->ReallyStarted() ) {
            $this->ReturnStakes();
            return null;
        }
        
        $em     = $this->doctrine->getManager();
        $dbGame = $this->gamePlayRepository->findOneBy( ['guid' => $this->Game->Id] );
        if ( $dbGame->getWinner() ) { // extra safety
            return [null, null];
        }
        
        $black = $this->playersRepository->find( $this->Game->Players[PlayerColor::Black->value]->Id );
        $white = $this->playersRepository->find( $this->Game->Players[PlayerColor::White->value]->Id );
        $computed = Score::NewScore(
            $black->getElo(),
            $white->getElo(),
            $black->getGameCount(),
            $white->getGameCount(),
            $color == PlayerColor::Black
        );
        $blackInc = 0;
        $whiteInc = 0;
        
        $black->increaseGameCount();
        $white->increaseGameCount();
        $dbGame->setWinner( $color == PlayerColor::Black ? 'Black' : 'White' );
        
        if ( $this->Game->IsGoldGame )
        {
            $blackInc = $computed['black'] - $black->getElo();
            $whiteInc = $computed['white'] - $white->getElo();
            
            $black->setElo( $computed['black'] );
            $white->setElo( $computed['white'] );
            
            $stake = $this->Game->Stake;
            $this->Game->Stake = 0;
            $this->logger->log( "Stake: {$stake}", 'EndGame' );
            $this->logger->log( "Initial gold: {$black->getGold()} {$this->Game->Players[PlayerColor::Black->value]->Gold} {$white->getGold()} {$this->Game->Players[PlayerColor::White->value]->Gold}", 'EndGame' );
            
            if ( $color == PlayerColor::Black ) {
                if ( ! $this->IsAi( $black->getGuid() ) ) {
                    $black->addGold( $stake );
                }
                $this->Game->Players[PlayerColor::Black->value]->Gold += $stake;
            } else {
                if ( ! $this->IsAi( $white->getGuid() ) ) {
                    $white->addGold( $stake );
                }
                $this->Game->Players[PlayerColor::White->value]->Gold += $stake;
            }
            $this->logger->log( "After transfer: {$black->getGold()} {$this->Game->Players[PlayerColor::Black->value]->Gold} {$white->getGold()} {$this->Game->Players[PlayerColor::White->value]->Gold}", 'EndGame' );
        }
        
        $em->persist( $black );
        $em->persist( $white );
        $em->persist( $dbGame );
        $em->flush();
        
        if ( $this->Game->IsGoldGame ) {
            $scoreBlack = new NewScoreDto();
            $scoreBlack->score = $black->getElo();
            $scoreBlack->increase = $blackInc;
            
            $scoreWhite = new NewScoreDto();
            $scoreWhite->score = $white->getElo();
            $scoreWhite->increase = $whiteInc;
            
            return [$scoreBlack, $scoreWhite];
        } else {
            return [null, null];
        }
    }
    
    protected function ReturnStakes(): void
    {
        $em     = $this->doctrine->getManager();
        
        //$this->logger->log( "Resign White Player: " . $this->Game->Players[PlayerColor::White->value], 'EndGame' );
        $black  = $this->playersRepository->find( $this->Game->Players[PlayerColor::Black->value]->Id );
        $white  = $this->Game->Players[PlayerColor::White->value] && $this->Game->Players[PlayerColor::White->value]->Id ?
        $this->playersRepository->find( $this->Game->Players[PlayerColor::White->value]->Id ) :
                    null;
        
        if ( ! $this->IsAi( $black->getGuid() ) ) {
            $black->setGold( $black->getGold() + $this->Game->Stake / 2 );
            $em->persist( $black );
        }
        
        if ( $white && ! $this->IsAi( $white->getGuid() ) ) {
            $white->setGold( $white->getGold() + $this->Game->Stake / 2 );
            $em->persist( $white );
        }
        
        $em->flush();
    }
    
    protected function Resign( PlayerColor $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner->value} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( PlayerColor $winner ): void
    {
        $this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner, $newScore );
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket ): void
    {
        if ( $socket != null ) {
            $this->logger->log( "Closing client", 'ExitGame' );
            $this->SetDbGameStatus( GamePlatform::GAME_ROOM_STATUS_WAITING );
            
            $socket->close( Frame::CLOSE_NORMAL );
            
            // FOR DEBUGGING
            if ( $socket instanceof WebsocketSwooleClient ) {
                return;
            }
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerColor::Black->value ) ) {
                $this->Clients->set( PlayerColor::Black->value, null );
            } else {
                $this->Clients->set( PlayerColor::White->value, null );
            }
        }
    }
    
    protected function CreateTempPlayer( GamePlayer $player, PlayerColor $playerPosition ): TempPlayer
    {
        if ( $this->Game->IsGoldGame && $player->getGold() < self::firstBet ) {
            throw new \RuntimeException( "Black player dont have enough gold" ); // Should be guarder earlier
        }
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $player->getGuid() ) ) {
            $player->setGold( self::firstBet );
        }
        
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setColor( $playerPosition->toString() );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
    
    protected function InitializePlayer( GamePlayer $dbUser, bool $aiUser, Player &$player ): void
    {
        $player->Id = $dbUser != null ? $dbUser->getId() : 0;
        $player->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
        $player->Name = $dbUser != null ? $dbUser->getName() : "Guest";
        $player->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
        $player->Elo = $dbUser != null ? $dbUser->getElo() : 0;
        
        if ( $this->Game->IsGoldGame ) {
            $player->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
        }
    }
    
    abstract protected function NewTurn( WebsocketClientInterface $socket ): void;
    
    abstract protected function GetWinner(): ?PlayerColor;
    
    abstract protected function SendWinner( PlayerColor $color, ?array $newScore ): void;
}
