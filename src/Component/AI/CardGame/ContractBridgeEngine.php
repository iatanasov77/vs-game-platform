<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\BidTrump;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\ContractBridgeGameMechanics\ValidCardsService;
use App\Component\Rules\CardGame\ContractBridgeGameMechanics\TrickWinnerService;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

// Contexts
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\BidTrumpExtensions;

// Strategies
use App\Component\AI\CardGame\ContractBridgeStrategies\NoTrumpsOursContractStrategy;
use App\Component\AI\CardGame\ContractBridgeStrategies\NoTrumpsTheirsContractStrategy;
use App\Component\AI\CardGame\ContractBridgeStrategies\TrumpOursContractStrategy;
use App\Component\AI\CardGame\ContractBridgeStrategies\TrumpTheirsContractStrategy;

class ContractBridgeEngine extends Engine
{
    /** @var ValidCardsService */
    private $validCardsService;
    
    /** @var TrickWinnerService */
    private $trickWinnerService;
    
    private IPlayStrategy $noTrumpsOursContractStrategy;
    private IPlayStrategy $noTrumpsTheirsContractStrategy;
    private IPlayStrategy $trumpOursContractStrategy;
    private IPlayStrategy $trumpTheirsContractStrategy;
    
    public function __construct( GameLogger $logger, Game $game )
    {
        parent::__construct( $logger, $game );
        
        $this->validCardsService    = new ValidCardsService();
        $this->trickWinnerService   = new TrickWinnerService();
        
        $this->noTrumpsOursContractStrategy = new NoTrumpsOursContractStrategy();
        $this->noTrumpsTheirsContractStrategy = new NoTrumpsTheirsContractStrategy();
        $this->trumpOursContractStrategy = new TrumpOursContractStrategy();
        $this->trumpTheirsContractStrategy = new TrumpTheirsContractStrategy();
    }
    
    public function DoBid(): BidTrump
    {
        $context = new PlayerGetBidContext();
        $context->MyPosition = $this->EngineGame->CurrentPlayer;
        $context->Bids = $this->EngineGame->Bids;
        $context->AvailableBids = $this->EngineGame->AvailableBids;
        $context->MyCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
        
        return $this->GetBid( $context );
    }
    
    public function PlayCard(): PlayCardAction
    {
        /*  
        $availableCards = $this->validCardsService->GetValidCards(
            $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value],
            $this->EngineGame->CurrentContract->Trump,
            $this->EngineGame->GetTrickActions()
        );
        */
        $availableCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
        
        $context = new PlayerPlayCardContext();
        $context->MyPosition = $this->EngineGame->CurrentPlayer;
        $context->Bids = $this->EngineGame->Bids;
        $context->CurrentContract = $this->EngineGame->CurrentContract;
        $context->MyCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
        //$context->Announces = $this->EngineGame->GetAvailableAnnounces( $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value] );
        $context->CurrentTrickActions = $this->EngineGame->GetTrickActions();
        $context->RoundActions = $this->EngineGame->GetTrickActions();
        $context->AvailableCardsToPlay = $availableCards;
        $context->CurrentTrickNumber = $this->EngineGame->trickNumber;
        
        $action = $this->_PlayCard( $context );
        
        // Update information after the action
        $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value]->removeElement( $action->Card );
        $action->Player = $this->EngineGame->CurrentPlayer;
        $action->TrickNumber = $this->EngineGame->GetTrickActionNumber() + 1;
        
        return $action;
    }
    
    protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void
    {
        
    }
    
    private function GetBid( PlayerGetBidContext $context ): BidTrump
    {
        $bid = BidTrump::Pass;
        
        return $bid;
    }
    
    private function _PlayCard( PlayerPlayCardContext $context ): PlayCardAction
    {
        // DEBUG
        return new PlayCardAction( $context->AvailableCardsToPlay->first(), false );
        
        $playedCards = new ArrayCollection();
        foreach ( $context->RoundActions as $action ) {
            if ( $action->TrickNumber < $context->CurrentTrickNumber ) {
                $playedCards[] = $action->Card;
            }
        }
        
        if ( $context->CurrentContract->Trump->has( BidTrump::AllTrumps ) ) {
            $strategy = PlayerPositionExtensions::IsInSameTeamWith( $context->CurrentContract->Player, $context->MyPosition )
                            ? $this->allTrumpsOursContractStrategy
                            : $this->allTrumpsTheirsContractStrategy;
        } else if ( $context->CurrentContract->Trump->has( BidTrump::NoTrumps ) ) {
            $strategy = PlayerPositionExtensions::IsInSameTeamWith( $context->CurrentContract->Player, $context->MyPosition )
                            ? $this->noTrumpsOursContractStrategy
                            : $this->noTrumpsTheirsContractStrategy;
        } else {
            // Trump contract
            $strategy = PlayerPositionExtensions::IsInSameTeamWith( $context->CurrentContract->Player, $context->MyPosition )
                            ? $this->trumpOursContractStrategy
                            : $this->trumpTheirsContractStrategy;
        }
        
        switch ( $context->CurrentTrickActions->count() ) {
            case 0:
                return $strategy->PlayFirst( $context, $playedCards );
                break;
            case 1:
                return $strategy->PlaySecond( $context, $playedCards );
                break;
            case 2:
                return $strategy->PlayThird(
                    $context,
                    $playedCards,
                    $this->trickWinnerService->GetWinner( $context->CurrentContract, $context->CurrentTrickActions )
                );
                break;
            default:
                return $strategy->PlayFourth(
                    $context,
                    $playedCards,
                    $this->trickWinnerService->GetWinner( $context->CurrentContract, $context->CurrentTrickActions )
                );
        }
        
        
        
        
        
        
        
        
        
        
        $suit = BidTrump::fromBitMaskValue( $context->CurrentContract->Trump->get() );
        $trumpSuit = BidTrumpExtensions::ToCardSuit( $suit );
        
        $cardsToPlayIterator = $context->AvailableCardsToPlay->getIterator();
        $cardsToPlayIterator->uasort( function ( $a, $b ) use ( $trumpSuit ) {
            return $b->NoTrumpOrder <=> $a->NoTrumpOrder;
        });
        $availableCards = new ArrayCollection( \iterator_to_array( $cardsToPlayIterator ) );
        
        return new PlayCardAction( $availableCards->first() );
    }
}
