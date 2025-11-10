<?php namespace App\Component\Rules\CardGame;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Rules\GameInterface;
use App\Component\GameLogger;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerGetAnnouncesContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;

use App\Component\Rules\CardGame\GameMechanics\RoundManager;
use App\Component\Rules\CardGame\GameMechanics\RoundResult;

use App\Component\Dto\Actions\PlayCardActionDto;

abstract class Game implements GameInterface
{
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    /** @var string */
    public $Id;
    
    /** @var Deck */
    public $Deck;
    
    /** @var Collection | Card[] */
    public $Pile;
    
    /** @var Collection | Card[] */
    public $SouthNorthTricks;
    
    /** @var Collection | Card[] */
    public $EastWestTricks;
    
    /** @var PlayerPosition */
    public $LastTrickWinner;
    
    /** @var array | Player[] */
    public array $Players;
    
    /** @var Collection | Card[] */
    public $playerCards;
    
    /** @var Bid */
    public $CurrentContract;
    
    /** @var Collection | Bid[] */
    public $AvailableBids;
    
    /** @var Collection | Card[] */
    public $ValidCards;
    
    /** @var Collection | Bid[] */
    public $Bids;
    
    /** @var int */
    public $ConsecutivePasses = 0;
    
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
    
    /** @var int */
    public $trickNumber;
    
    /** @var GameLogger */
    protected  $logger;
    
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    
    /** @var RoundManager */
    protected $roundManager;
    
    public function __construct( GameLogger $logger, EventDispatcherInterface $eventDispatcher )
    {
        $this->logger           = $logger;
        $this->eventDispatcher  = $eventDispatcher;
    }
    
    abstract public function NextPlayer(): PlayerPosition;
    
    public function SetStartPosition(): void
    {
        $this->PlayGame();
    }
    
    public function PlayGame( PlayerPosition $firstToPlay = PlayerPosition::South ): void
    {
        $this->roundManager = new RoundManager( $this, $this->logger, $this->eventDispatcher );
        
        $this->firstInRound = $firstToPlay;
        $this->roundNumber = 1;
        $this->trickNumber = 1;
    }
    
    public function SwitchPlayer(): void
    {
        $this->logger->log( 'SwitchPlayer Called !!!', 'SwitchPlayer' );
        $this->CurrentPlayer = $this->NextPlayer();
    }
    
    public function SetFirstBidWinner(): void
    {
        if ( $this->PlayState == GameState::firstBid ) {
            if ( $this->roundNumber == 1 ) {
                $this->CurrentPlayer = PlayerPosition::South;
                //$this->CurrentPlayer = PlayerPosition::from( \rand( 0, 3 ) );
            } else {
                $this->CurrentPlayer = $this->firstInRound;
                $this->CurrentPlayer = $this->NextPlayer();
                $this->firstInRound = $this->CurrentPlayer;
            }
            
            $this->PlayRound();
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
    
    public function PlayRound(): ?PlayerPosition
    {
        return $this->roundManager->PlayRound();
    }
    
    public function SetContract( Bid $bid, PlayerPosition $nextPlayer ): void
    {
        $this->roundManager->SetContract( $bid, $nextPlayer );
    }
    
    public function GetValidCards( Collection $playerCards, Bid $currentContract, Collection $trickActions ): Collection
    {
        return $this->roundManager->GetValidCards( $playerCards, $currentContract, $trickActions );
    }
    
    public function GetAvailableAnnounces( Collection $playerCards ): Collection
    {
        return $this->roundManager->GetAvailableAnnounces( $playerCards );
    }
    
    public function GetBid( PlayerGetBidContext $context ): BidType
    {
        return BidType::Pass;
    }
    
    public function GetAnnounces( PlayerGetAnnouncesContext $context ): Collection
    {
        $availableAnnounces = $context->AvailableAnnounces;
        
        return $availableAnnounces;
    }
    
    public function PlayCard( PlayerPlayCardContext $context ): PlayCardActionDto
    {
        $action = new PlayCardActionDto();
        
        return $action;
    }
    
    public function GetTrickActionNumber(): int
    {
        return $this->roundManager->GetTrickActionNumber();
    }
    
    public function GetTrickActions(): Collection
    {
        return $this->roundManager->GetTrickActions();
    }
    
    public function AddTrickAction( PlayCardAction $action ): void
    {
        $this->roundManager->AddTrickAction( $action );
    }
    
    public function EndOfTrick( Collection $trickActions ): void
    {
        
    }
    
    public function EndOfRound( RoundResult $roundResult ): void
    {
        
    }
    
    public function EndOfGame( GameResult $gameResult ): void
    {
        
    }
}
