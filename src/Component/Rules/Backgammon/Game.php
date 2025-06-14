<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

abstract class Game
{
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    /** @var string */
    public $Id;
    
    /** @var GamePlayer */
    public $BlackPlayer;
    
    /** @var GamePlayer */
    public $WhitePlayer;
    
    /** @var PlayerColor */
    public $CurrentPlayer;
    
    /** @var Collection | Point[] */
    public $Points;
    
    /** @var Collection | Dice[] */
    public $Roll;
    
    /** @var Collection | Move[] */
    public $ValidMoves;
    
    /** @var GameState */
    public $PlayState = GameState::FirstThrow;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var \DateTime */
    public $ThinkStart;
    
    /** @var Collection | Point[] */
    public $Bars;
    
    /** @var int */
    public $GoldMultiplier;
    
    /** @var bool */
    public $IsGoldGame;
    
    /** @var PlayerColor */
    public $LastDoubler = null;
    
    /** @var int */
    public $Stake;
    
    /** @var int */
    public $WhiteStarts = 0;
    
    /** @var int */
    public $BlackStarts = 0;
    
    /** @var GameLogger */
    protected  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger   = $logger;
    }
    
    public static function CalcPointsLeft( Game &$game ): void
    {
        foreach ( $game->Points as $point ) {
            $blackCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry && $entry->Color === PlayerColor::Black;
                }
            );
            foreach ( $blackCheckers as $ckr ) {
                $game->BlackPlayer->PointsLeft += 25 - $point->BlackNumber;
            }
            
            $whiteCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry && $entry->Color === PlayerColor::White;
                }
            );
            foreach ( $whiteCheckers as $ckr ) {
                $game->WhitePlayer->PointsLeft += 25 - $point->WhiteNumber;
            }
        }
    }
    
    abstract public function GenerateMoves(): array;
    
    public function SwitchPlayer(): void
    {
        $this->CurrentPlayer = $this->OtherPlayer();
    }
    
    public function OtherPlayer(): PlayerColor
    {
        return $this->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
    }
    
    public function SetStartPosition(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
        
        $this->AddCheckers( 2, PlayerColor::Black, 1 );
        $this->AddCheckers( 2, PlayerColor::White, 1 );
        
        $this->AddCheckers( 5, PlayerColor::Black, 12 );
        $this->AddCheckers( 5, PlayerColor::White, 12 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 17 );
        $this->AddCheckers( 3, PlayerColor::White, 17 );
        
        $this->AddCheckers( 5, PlayerColor::Black, 19 );
        $this->AddCheckers( 5, PlayerColor::White, 19 );
        
        // CloseToVictory();
        // DebugBar();
        //DebugBlocked();
        // DebugBearingOff();
        // AtHomeAndOtherAtBar();
        // AtHomeAndOtherAtBar2();
        // Test();
        // LegalMove();
    }
    
    public function ClearCheckers(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
    }
    
    public function IsBearingOff( PlayerColor $color ): bool
    {
        // Points that have checkers with the color asked all have higher number than 18
        $colorCheckers  = $this->Points->filter(
            function( $entry ) use ( $color ) {
                $askedColor = false;
                
                foreach ( $entry->Checkers as $checker ) {
                    $askedColor = $checker ? $checker->Color == $color : false;
                }
                
                return $askedColor;
            }
            )->filter(
                function( $entry ) use ( $color ) {
                    return $entry->GetNumber( $color ) < 19;
                }
                );
            
            return $colorCheckers->isEmpty();
    }
    
    abstract public function AddCheckers( int $count, PlayerColor $color, int $point ): void;
    
    public function SetFirstRollWinner(): void
    {
        // $this->logger->log( 'MyDebug Existing Rolls: ' . \print_r( $this->Roll, true ), 'FirstThrowState' );
        
        if ( $this->PlayState == GameState::FirstThrow ) {
            if ( $this->Roll[0]->Value > $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::Black;
                $this->BlackStarts++;
            } else if ( $this->Roll[0]->Value < $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::White;
                $this->WhiteStarts++;
            }
            
            if ( $this->Roll[0]->Value != $this->Roll[1]->Value ) {
                $this->PlayState = GameState::Playing;
            }
        }
    }
    
    public function FakeRoll( int $v1, int $v2 ): void
    {
        $this->Roll = Dice::GetDices( $v1, $v2 );
        $this->SetFirstRollWinner();
    }
    
    public function RollDice(): void
    {
        /* Test With Concreate Dices 
        $this->FakeRoll( 1, 2 );
        */
        $this->Roll = Dice::Roll();
        $this->SetFirstRollWinner();
        
        // $this->logger->log( 'CurrentPlayer: ' . $this->CurrentPlayer->value, 'FirstThrowState' );
        $this->ClearMoves( $this->ValidMoves );
        $this->_GenerateMoves( $this->ValidMoves );
    }
    
    public function GetHome( PlayerColor $color ): Point
    {
        return $this->Points->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) == 25;
            }
            )->first();
    }
    
    public function PlayersPassed(): bool
    {
        $lastBlack = 0;
        $lastWhite = 0;
        
        for ( $i = 0; $i < 25; $i++ ) {
            $checker    = $this->Points[$i]->Checkers->filter(
                function( $entry ) {
                    return $entry && $entry->Color === PlayerColor::Black;
                }
            );
            
            if ( $checker ) {
                $lastBlack = Points[i].GetNumber( PlayerColor::Black );
                break;
            }
        }
        
        
        for ( $i = 25 - 1; $i >= 1; $i-- ) {
            $checker    = $this->Points[$i]->Checkers->filter(
                function( $entry ) {
                    return $entry && $entry->Color === PlayerColor::White;
                }
            );
            
            if ( $checker ) {
                $lastWhite = $this->Points[$i].GetNumber( PlayerColor::Black );
                break;
            }
        }
        
        return $lastBlack > $lastWhite;
    }
    
    public function ReallyStarted(): bool
    {
        return $this->BlackPlayer->FirstMoveMade && $this->WhitePlayer->FirstMoveMade;
    }
    
    protected function ClearMoves( Collection &$moves ): void
    {
        // This will probably make it a lot easier for GC, and might even prevent memory leaks
        foreach ( $moves as $move ) {
            if ( $move->NextMoves != null && ! empty( $move->NextMoves ) ) {
                $this->ClearMoves( $move->NextMoves );
                $move->NextMoves->clear();
            }
        }
        $moves->clear();
    }
    
    protected function AtHomeAndOtherAtBar(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 2, PlayerColor::Black, 22 );
        $this->AddCheckers( 5, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::Black, 25 );
        
        $this->AddCheckers( 2, PlayerColor::White, 19 );
        $this->AddCheckers( 2, PlayerColor::White, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        $this->AddCheckers( 2, PlayerColor::White, 22 );
        $this->AddCheckers( 2, PlayerColor::White, 23 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 0 );
        
    }
    
    protected function OneMoveToVictory(): void
    {
        //Only one move to victory
        $this->AddCheckers( 14, PlayerColor::Black, 25 );
        $this->AddCheckers( 14, PlayerColor::White, 25 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 24 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
    }
    
    protected function DebugBlocked(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 0 );
        $this->AddCheckers( 1, PlayerColor::White, 0 );
    }
    
    protected function DebugBearingOff(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 19 );
        $this->AddCheckers( 1, PlayerColor::White, 19 );
    }
    
    abstract protected function _GenerateMoves( Collection &$moves ): void;
}
