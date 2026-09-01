<?php namespace App\Component\Rules\CardGame;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use BitMask\EnumBitMask;

use App\Component\Rules\GameInterface;
use App\Component\GameLogger;
use App\Component\GameVariant;
use App\Component\PlayerPositions;
use App\Component\Type\GameState;
use App\Component\Type\PlayerPosition;
use App\Component\Type\PlayerDirection;
use App\Component\Type\BidTrump;

use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerGetAnnouncesContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;

use App\Component\Rules\CardGame\BridgeBeloteGameMechanics\RoundManager as BridgeBeloteRoundManager;
use App\Component\Rules\CardGame\ContractBridgeGameMechanics\RoundManager as ContractBridgeRoundManager;

use App\Component\Dto\Actions\PlayCardActionDto;

class Game implements GameInterface
{
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    /** @var string */
    public $Id;
    
    /** @var string */
    public $GameCode;
    
    /** @var PlayerDirection */
    public $PlayerDirection;
    
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
    public $BidHistory;
    
    /** @var Collection | Bid[] */
    public $Bids;
    
    /** @var int */
    public $ConsecutivePasses = 0;
    
    /** @var PlayerPosition */
    public $CurrentPlayer;
    
    /** @var PlayerPosition */
    public $DummyPlayer;
    
    /** @var PlayerPosition */
    public $DummyOwner;
    
    /** @var bool */
    public $DummyFaceup = false;
    
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
    
    /** @var int */
    public $southNorthPoints;
    
    /** @var int */
    public $eastWestPoints;
    
    /** @var int */
    public $hangingPoints;
    
    /** @var Collection | Announce[] */
    public $announces;
    
    /** @var GameLogger */
    protected  $logger;
    
    /** @var EventDispatcherInterface */
    protected $eventDispatcher;
    
    /** @var BridgeBeloteRoundManager */
    protected $bridgeBeloteRoundManager;
    
    /** @var ContractBridgeRoundManager */
    protected $contractBridgeRoundManager;
    
    public function __construct( GameLogger $logger, EventDispatcherInterface $eventDispatcher )
    {
        $this->logger           = $logger;
        $this->eventDispatcher  = $eventDispatcher;
    }
    
    public function NextPlayer(): PlayerPosition
    {
        if ( $this->PlayerDirection === PlayerDirection::Clockwise ) {
            return PlayerPositions::Next( $this->CurrentPlayer );
        }
        
        return PlayerPositions::Prev( $this->CurrentPlayer );
    }
    
    public function PlayGame( PlayerPosition $firstToPlay = PlayerPosition::South ): void
    {
        switch ( $this->GameCode ) {
            case GameVariant::BRIDGE_BELOTE_CODE:
                $this->bridgeBeloteRoundManager = new BridgeBeloteRoundManager( $this, $this->logger, $this->eventDispatcher );
                break;
            case GameVariant::CONTRACT_BRIDGE_CODE:
                $this->contractBridgeRoundManager = new ContractBridgeRoundManager( $this, $this->logger, $this->eventDispatcher );
                break;
        }
        
        $this->firstInRound = $firstToPlay;
        $this->roundNumber = 1;
        $this->trickNumber = 1;
        
        $this->southNorthPoints = 0;
        $this->eastWestPoints = 0;
        $this->hangingPoints = 0;
        $this->announces = new ArrayCollection();
    }
    
    public function SetStartPosition(): void
    {
        $this->PlayGame();
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
    
    public function PlayRound(): ?PlayerPosition
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                return $this->contractBridgeRoundManager->PlayRound();
                break;
            default:
                return $this->bridgeBeloteRoundManager->PlayRound();
        }
    }
    
    public function SetContract( Bid $bid, PlayerPosition $nextPlayer ): void
    {
        $this->logger->log( "SetContract Bid Value: {$bid->Value}", 'RoundManager' );
        
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                $this->contractBridgeRoundManager->SetContract( $bid, $nextPlayer );
                break;
            default:
                $this->bridgeBeloteRoundManager->SetContract( $bid, $nextPlayer );
        }
    }
    
    public function GetValidCards( Collection $playerCards, Bid $currentContract, Collection $trickActions ): Collection
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                return $this->contractBridgeRoundManager->GetValidCards( $playerCards, $currentContract, $trickActions );
                break;
            default:
                return $this->bridgeBeloteRoundManager->GetValidCards( $playerCards, $currentContract, $trickActions );
        }
    }
    
    public function GetAvailableAnnounces( Collection $playerCards ): Collection
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                //return $this->contractBridgeRoundManager->GetAvailableAnnounces( $playerCards );
                return new ArrayCollection();
                break;
            default:
                return $this->bridgeBeloteRoundManager->GetAvailableAnnounces( $playerCards );
        }
    }
     
    public function GetBid( PlayerGetBidContext $context ): BidTrump
    {
        return BidTrump::Pass;
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
    
    public function IsBeloteAllowed( Collection $playerCards, EnumBitMask $contract, Collection $currentTrickActions, Card $playedCard ): bool
    {
        return $this->bridgeBeloteRoundManager->IsBeloteAllowed( $playerCards, $contract, $currentTrickActions, $playedCard );
    }
    
    public function GetTrickActionNumber(): int
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                return $this->contractBridgeRoundManager->GetTrickActionNumber();
                break;
            default:
                return $this->bridgeBeloteRoundManager->GetTrickActionNumber();
        }
    }
    
    public function GetTrickActions(): Collection
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                return $this->contractBridgeRoundManager->GetTrickActions();
                break;
            default:
                return $this->bridgeBeloteRoundManager->GetTrickActions();
        }
    }
    
    public function AddTrickAction( PlayCardAction $action ): void
    {
        switch ( $this->GameCode ) {
            case GameVariant::CONTRACT_BRIDGE_CODE:
                $this->contractBridgeRoundManager->AddTrickAction( $action );
                break;
            default:
                $this->bridgeBeloteRoundManager->AddTrickAction( $action );
        }
    }
    
    public function EndOfTrick( Collection $trickActions ): void
    {
        
    }
    
    public function EndOfRound( RoundResult $roundResult ): void
    {
        
    }
    
    public function GetNewScore(): RoundResult
    {
        return $this->bridgeBeloteRoundManager->GetScore(
            $this->CurrentContract,
            $this->SouthNorthTricks,
            $this->EastWestTricks,
            $this->announces,
            $this->hangingPoints,
            $this->LastTrickWinner
        );
    }
    
    public function EndOfGame( GameResult $gameResult ): void
    {
        
    }
}
