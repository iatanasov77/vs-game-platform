<?php namespace App\Component\Manager;

use Ratchet\RFC6455\Messaging\Frame;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\BoardGame\Score;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Utils\Guid;

use App\Entity\GamePlayer;
use App\Entity\TempPlayer;
use App\Component\Rules\BoardGame\Player;

abstract class BoardGameManager extends AbstractGameManager
{
    protected function CreateDbGame(): void
    {
        $blackPlayer = $this->CreateTempPlayer( $this->Game->BlackPlayer->Id, PlayerColor::Black->value );
        $whitePlayer = $this->CreateTempPlayer( $this->Game->WhitePlayer->Id, PlayerColor::White->value );
        
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( $this->Game->Id );
        
        $blackPlayer->setGame( $game );
        $whitePlayer->setGame( $game );
        
        $game->addGamePlayer( $blackPlayer );
        $game->addGamePlayer( $whitePlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
    }
    
    protected function IsAi( ?string $guid ): bool
    {
        return $guid == GamePlayer::AiUser;
    }
    
    protected function AisTurn(): bool
    {
        $plyr = $this->Game->CurrentPlayer == PlayerColor::Black ? $this->Game->BlackPlayer : $this->Game->WhitePlayer;
        $this->logger->log( "AisTurn CurrentPlayer: " . \print_r( $plyr, true ) , 'SwitchPlayer' );
        
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
        
        $black = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        $white = $this->playersRepository->find( $this->Game->WhitePlayer->Id );
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
            $this->logger->log( "Initial gold: {$black->getGold()} {$this->Game->BlackPlayer->Gold} {$white->getGold()} {$this->Game->WhitePlayer->Gold}", 'EndGame' );
            
            if ( $color == PlayerColor::Black ) {
                if ( ! $this->IsAi( $black->getGuid() ) ) {
                    $black->addGold( $stake );
                }
                $this->Game->BlackPlayer->Gold += $stake;
            } else {
                if ( ! $this->IsAi( $white->getGuid() ) ) {
                    $white->addGold( $stake );
                }
                $this->Game->WhitePlayer->Gold += $stake;
            }
            $this->logger->log( "After transfer: {$black->getGold()} {$this->Game->BlackPlayer->Gold} {$white->getGold()} {$this->Game->WhitePlayer->Gold}", 'EndGame' );
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
        
        //$this->logger->log( "Resign White Player: " . $this->Game->WhitePlayer, 'EndGame' );
        $black  = $this->playersRepository->find( $this->Game->BlackPlayer->Id );
        $white  = $this->Game->WhitePlayer && $this->Game->WhitePlayer->Id ?
                    $this->playersRepository->find( $this->Game->WhitePlayer->Id ) :
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
            $socket->close( Frame::CLOSE_NORMAL );
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerColor::Black->value ) ) {
                $this->Clients->set( PlayerColor::Black->value, null );
            } else {
                $this->Clients->set( PlayerColor::White->value, null );
            }
        }
    }
    
    protected function CreateTempPlayer( int $playerId, int $playerPositionId ): TempPlayer
    {
        $player = $this->playersRepository->find( $playerId );
        
        if ( $this->Game->IsGoldGame && $player->getGold() < self::firstBet ) {
            throw new \RuntimeException( "Black player dont have enough gold" ); // Should be guarder earlier
        }
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $player->getGuid() ) ) {
            $player->setGold( self::firstBet );
        }
        
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setColor( $playerPositionId );
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
