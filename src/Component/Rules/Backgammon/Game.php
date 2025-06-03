<?php namespace App\Component\Rules\Backgammon;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

abstract class Game
{
    /** @var string */
    protected $environement;
    
    /** @var LoggerInterface */
    protected  $logger;
    
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
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    public function __construct( string $environement, LoggerInterface $logger )
    {
        $this->environement = $environement;
        $this->logger       = $logger;
    }
    
    public static function CalcPointsLeft( Game &$game ): void
    {
        foreach ( $game->Points as $point ) {
            $blackCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry->Color == PlayerColor::Black;
                }
                );
            foreach ( $blackCheckers as $ckr ) {
                $game->BlackPlayer->PointsLeft += 25 - $point->BlackNumber;
            }
            
            $whiteCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry->Color == PlayerColor::White;
                }
                );
            foreach ( $whiteCheckers as $ckr ) {
                $game->WhitePlayer->PointsLeft += 25 - $point->WhiteNumber;
            }
        }
    }
    
    abstract public function GenerateMoves(): array;
    
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
    
    public function FakeRoll( int $v1, int $v2 ): void
    {
        $this->Roll = new ArrayCollection( Dice::GetDices( $v1, $v2 )->toArray() );
        $this->SetFirstRollWinner();
    }
    
    public function SetFirstRollWinner(): void
    {
        $this->log( 'MyDebug Existing Rolls: ' . \print_r( $this->Roll, true ) );
        
        if ( $this->PlayState == GameState::FirstThrow ) {
            if ( $this->Roll[0]->Value > $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::Black;
            } else if ( $this->Roll[0]->Value < $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::White;
            }
            
            if ( $this->Roll[0]->Value != $this->Roll[1]->Value ) {
                $this->PlayState = GameState::Playing;
            }
        }
    }
    
    public function RollDice(): void
    {
        $this->Roll = new ArrayCollection( Dice::Roll()->toArray() );
        $this->SetFirstRollWinner();
        
        $this->ClearMoves( $this->ValidMoves );
        $this->_GenerateMoves( $this->ValidMoves );
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
    
    protected function log( $logData ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( $logData );
        }
    }
}
