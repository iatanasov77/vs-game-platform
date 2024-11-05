<?php namespace App\Component\Rules\Backgammon;

use Psr\Log\LoggerInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

class Game
{
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
    
    public function __construct( LoggerInterface $logger )
    {
        $this->logger   = $logger;
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
}
