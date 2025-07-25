<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;

abstract class Game
{
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    /** @var int */
    public static $DebugValidMoves = 0;
    
    /** @var string */
    public $Id;
    
    /** @var Player */
    public $BlackPlayer;
    
    /** @var Player */
    public $WhitePlayer;
    
    /** @var PlayerColor */
    public $CurrentPlayer;
    
    /** @var Collection | Point[] */
    protected $Points;
    
    /** @var Collection | Dice[] */
    public $Roll;
    
    /** @var Collection | Move[] */
    public $ValidMoves;
    
    /** @var GameState */
    public $PlayState = GameState::firstThrow;
    
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
    
    public function __set( $name, $value )
    {
        switch ( $name ) {
            case 'Points':
                $this->Points = $value;
                
                $trace = debug_backtrace();
                //$this->logger->log( "Points Changed in File: {$trace[0]['file']} on line {$trace[0]['line']}", 'GenerateMoves' );
                
                break;
            default:
                throw new \RuntimeException( 'Undefined Property of Game Rules !!!' );
        }
    }
    
    public function __get( $name )
    {
        switch ( $name ) {
            case 'Points':
                return $this->Points;
                break;
            default:
                throw new \RuntimeException( 'Undefined Property of Game Rules !!!' );
        }
    }
    
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
    
    abstract public function SetStartPosition(): void;
    
    abstract public function GenerateMoves(): array;
    
    abstract public function MakeMove( Move &$move ): ?Checker;
    
    public function SwitchPlayer(): void
    {
        $this->logger->log( 'SwitchPlayer Called !!!', 'SwitchPlayer' );
        $this->CurrentPlayer = $this->OtherPlayer();
    }
    
    public function OtherPlayer(): PlayerColor
    {
        return $this->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
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
        
        return $colorCheckers->isEmpty(); // all have higher number than 18
    }
    
    abstract public function AddCheckers( int $count, PlayerColor $color, int $point ): void;
    
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
