<?php namespace App\Component\Manager;

use Ratchet\RFC6455\Messaging\Frame;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\BoardGame\Score;

// Types
use App\Component\Type\PlayerColor;
use App\Component\Type\GameState;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\toplist\NewScoreDto;
use App\Component\Dto\Actions\GameEndedActionDto;

abstract class BoardGameManager extends AbstractGameManager
{
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
    
    protected function GetWinner(): ?PlayerColor
    {
        $winner = null;
        if ( $this->Game->CurrentPlayer == PlayerColor::Black ) {
            if (
                $this->Game->GetHome( PlayerColor::Black )->Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::Black;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::ended;
                $winner = PlayerColor::Black;
            }
        } else {
            if (
                $this->Game->GetHome( PlayerColor::White )->Checkers->filter(
                    function( $entry ) {
                        return $entry->Color == PlayerColor::White;
                    }
                )->count() == 15
            ) {
                $this->Game->PlayState = GameState::ended;
                $winner = PlayerColor::White;
            }
        }
        
        return $winner;
    }
    
    protected function SendWinner( PlayerColor $color, ?array $newScore ): void
    {
        $game = Mapper::BoardGameToDto( $this->Game );
        $game->winner = $color;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        $gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerColor::Black->value ), $gameEndedAction );
        
        $gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerColor::White->value ), $gameEndedAction );
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
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
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
}
