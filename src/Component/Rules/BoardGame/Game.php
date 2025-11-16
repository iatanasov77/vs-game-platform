<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Rules\GameInterface;
use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Component\Rules\BoardGame\Helper as GameHelper;

abstract class Game implements GameInterface
{
    use GameHelper;
    
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
    public $Points;
    
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
    
    abstract public function UndoMove( Move &$move, ?Checker $hitChecker ): void;
    
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
                $lastBlack = $this->Points[$i]->GetNumber( PlayerColor::Black );
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
                $lastWhite = $this->Points[$i]->GetNumber( PlayerColor::Black );
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
    
    abstract protected function _GenerateMoves( Collection &$moves ): void;
}
