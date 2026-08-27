<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\BidTrump;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\ValidCardsService;
use App\Component\Rules\CardGame\ContractBridgeGameMechanics\TrickWinnerService;

// Contexts
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\BidTrumpExtensions;

class ContractBridgeEngine extends Engine
{
    /** @var ValidCardsService */
    private $validCardsService;
    
    public function __construct( GameLogger $logger, Game $game )
    {
        parent::__construct( $logger, $game );
        
        $this->validCardsService = new ValidCardsService();
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
        
        //$action = $this->_PlayCard( $context );
        $action = new PlayCardAction( $availableCards->first(), false );
        
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
