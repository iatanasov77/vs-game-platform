<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Manager\AbstractGameManager;

abstract class BackgammonGame extends Game
{
    public function AddCheckers( int $count, PlayerColor $color, int $point ): void
    {
        $checker        = new Checker();
        $checker->Color = $color;
        
        for ( $i = 0; $i < $count; $i++ ) {
            $this->Points->filter(
                function( $entry ) use ( $color, $point ) {
                    return $entry->GetNumber( $color ) == $point;
                }
            )->first()->Checkers[]  = $checker;
        }
        
        //$this->logger->debug( $this->Points, 'PointsAddCheckers.txt' );
    }
    
    public function GenerateMoves(): array
    {
        $moves = new ArrayCollection();
        $this->_GenerateMoves( $moves );
        
        // Making sure both dice are played
        if ( $moves->NextMoves->count() ) {
            $moves = $moves->filter(
                function( $entry ) {
                    return $entry->NextMoves->count() > 0;
                }
            )->toArray();
        } else if ( $moves->count() ) {
            // All moves have zero next move in this block
            // Only one dice can be use and it must be the one with highest value
            
            $currentPlayer  = $this->CurrentPlayer;
            $this->logger->log( 'CurrentPlayer: ' . \print_r( $currentPlayer, true ), 'GamePlay' );
            $moves = $moves->filter(
                function( $entry ) use ( $currentPlayer ) {
                    return $entry->To->GetNumber( $currentPlayer ) - $entry->From->GetNumber( $currentPlayer );
                }
            );
            $first = $moves->getMovesOrdered( $moves, AbstractGameManager::COLLECTION_ORDER_ASC )->first();
            $moves->clear();
            $moves[] = $first;
        }
        
        return $moves;
    }
    
    public function SetFirstRollWinner(): void
    {
        // $this->logger->log( 'Existing Rolls: ' . \print_r( $this->Roll, true ), 'FirstThrowState' );
        
        if ( $this->PlayState == GameState::firstThrow ) {
            if ( $this->Roll[0]->Value > $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::Black;
                $this->BlackStarts++;
            } else if ( $this->Roll[0]->Value < $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::White;
                $this->WhiteStarts++;
            }
            
            if ( $this->Roll[0]->Value != $this->Roll[1]->Value ) {
                $this->PlayState = GameState::playing;
            }
        }
    }
    
    public function FakeRoll( int $v1, int $v2 ): void
    {
        $this->Roll = new ArrayCollection( Dice::GetDices( $v1, $v2 ) );
        $this->SetFirstRollWinner();
    }
    
    public function RollDice(): void
    {
        $this->Roll = new ArrayCollection( Dice::Roll() );
        $this->SetFirstRollWinner();
        
        // $this->logger->log( 'CurrentPlayer: ' . $this->CurrentPlayer->value, 'FirstThrowState' );
        $this->ClearMoves( $this->ValidMoves );
        $this->_GenerateMoves( $this->ValidMoves );
        
        Game::$DebugValidMoves++;
        //$this->logger->debug( $this->ValidMoves, 'ValidMoves_' . Game::$DebugValidMoves .  '.txt' );
    }
    
    public function GetHome( PlayerColor $color ): Point
    {
        return $this->Points->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) == 25;
            }
        )->first();
    }
}
