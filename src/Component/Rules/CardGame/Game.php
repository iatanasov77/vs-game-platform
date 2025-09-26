<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Rules\GameInterface;
use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;

use App\Component\Rules\CardGame\GameMechanics\RoundManager;

abstract class Game implements GameInterface
{
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    /** @var string */
    public $Id;
    
    /** @var array */
    public $pile;
    
    /** @var array | Player[] */
    public array $Players;
    
    /**
     * Tricks Of Cards
     * 
     * $teamsTricks[0] for Team1 (North-South)
     * $teamsTricks[1] for Team2 (East-West)
     *
     * @var array
     */
    public $teamsTricks;
    
    /** @var PlayerPosition */
    public $CurrentPlayer;
    
    /** @var GameState */
    public $PlayState = GameState::firstBid;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var \DateTime */
    public $ThinkStart;
    
    /** @var int */
    public $GoldMultiplier;
    
    /** @var bool */
    public $IsGoldGame;
    
    /** @var PlayerPosition */
    public $firstInRound;
    
    /** @var int */
    public $roundNumber;
    
    /** @var GameLogger */
    protected  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger   = $logger;
    }
    
    abstract public function SetStartPosition(): void;
    
    abstract public function NextPlayer(): PlayerPosition;
    
    public function SwitchPlayer(): void
    {
        $this->logger->log( 'SwitchPlayer Called !!!', 'SwitchPlayer' );
        $this->CurrentPlayer = $this->OtherPlayer();
    }
    
    public function SetFirstBidWinner(): void
    {
        if ( $this->PlayState == GameState::firstBid ) {
            $this->CurrentPlayer = PlayerPosition::South;
            //$this->CurrentPlayer = PlayerPosition::from( \rand( 0, 3 ) );
            $this->PlayState = GameState::bidding;
        }
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
}
