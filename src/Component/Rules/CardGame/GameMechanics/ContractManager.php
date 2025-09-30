<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\BelotGameException;
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

class ContractManager
{
    use PlayerPositionExtensions;
    
    private Game $game;
    
    public function __construct( Game $game )
    {
        $this->game = $game;
    }
    
    public function GetContract(
        int $roundNumber,
        PlayerPosition $firstToPlay,
        int $southNorthPoints,
        int $eastWestPoints,
        Collection $playerCards,
        Collection &$bids
    ): Bid {
        $bids = new ArrayCollection(); // List<Bid>(8);
        $consecutivePasses = 0;
        $currentPlayerPosition = $firstToPlay;
        $contract = new Bid( $currentPlayerPosition, BidType::Pass );
        
        $bidContext = new PlayerGetBidContext();
        $bidContext->RoundNumber = $roundNumber;
        $bidContext->FirstToPlayInTheRound = $firstToPlay;
        $bidContext->EastWestPoints = $eastWestPoints;
        $bidContext->SouthNorthPoints = $southNorthPoints;
        $bidContext->Bids = $bids;
        
        $availableBids = $this->GetAvailableBids( $contract, $currentPlayerPosition );
        
        // Debugging
        $bids = $availableBids;
        return $contract;
        
        if ( $availableBids->count() == 1 ) { // $availableBids == BidType::Pass
            // Only pass is available so we don't ask the player
            $bid = BidType::Pass;
        } else {
            // Prepare context
            $bidContext->AvailableBids = $availableBids;
            $bidContext->MyCards = $playerCards[$currentPlayerPosition->value];
            $bidContext->MyPosition = $currentPlayerPosition;
            $bidContext->CurrentContract = $contract;
            
            // Execute GetBid()
            $bid = $this->game->Players[$currentPlayerPosition->value]->GetBid( $bidContext );
            
            // Validate
            if ( $bid != BidType::Pass && ( $bid & ( $bid - 1 ) ) != 0 ) {
                throw new BelotGameException( "Invalid bid from {$currentPlayerPosition->value} player. More than 1 flags returned." );
            }
            
            if ( ! $availableBids->has( $bid ) ) {
                throw new BelotGameException( "Invalid bid from {$currentPlayerPosition->value} player. This bid is not permitted." );
            }
            
            if ( $bid == BidType::Double || $bid == BidType::ReDouble ) {
                $contract->Type->remove( BidType::Double );
                $contract->Type->remove( BidType::ReDouble );
                $contract->Type->set( $bid );
                $contract->Player = $currentPlayerPosition;
            } else if ( $bid != BidType::Pass ) {
                $contract->Type = $bid;
                $contract->Player = $currentPlayerPosition;
            }
        }
        
        $bids[] = new Bid( $currentPlayerPosition, $bid );
        
        $consecutivePasses = ( $bid == BidType::Pass) ? $consecutivePasses + 1 : 0;
        
        return $contract;
    }
    
    private function GetAvailableBids( Bid $currentContract, PlayerPosition $currentPlayer ): Collection
    {
        $cleanContract = $currentContract->Type;
        
        $cleanContract->remove( BidType::Double );
        $cleanContract->remove( BidType::ReDouble );
        
        $availableBids = new ArrayCollection();
        $availableBids->set( BidType::Pass->value(), new Bid( $currentPlayer, BidType::Pass ) );
        
        if ( $cleanContract->get() < BidType::Clubs->bitMaskValue() ) {
            $availableBids->set( BidType::Clubs->value(), new Bid( $currentPlayer, BidType::Clubs ) );
        }
        
        if ( $cleanContract->get() < BidType::Diamonds->bitMaskValue() ) {
            $availableBids->set( BidType::Diamonds->value(), new Bid( $currentPlayer, BidType::Diamonds ) );
        }
        
        if ( $cleanContract->get() < BidType::Hearts->bitMaskValue() ) {
            $availableBids->set( BidType::Hearts->value(), new Bid( $currentPlayer, BidType::Hearts ) );
        }
        
        if ( $cleanContract->get() < BidType::Spades->bitMaskValue() ) {
            $availableBids->set( BidType::Spades->value(), new Bid( $currentPlayer, BidType::Spades ) );
        }
        
        if ( $cleanContract->get() < BidType::NoTrumps->bitMaskValue() ) {
            $availableBids->set( BidType::NoTrumps->value(), new Bid( $currentPlayer, BidType::NoTrumps ) );
        }
        
        if ( $cleanContract->get() < BidType::AllTrumps->bitMaskValue() ) {
            $availableBids->set( BidType::AllTrumps->value(), new Bid( $currentPlayer, BidType::AllTrumps ) );
        }
        
        if (
            ! $this->IsInSameTeamWith( $currentPlayer, $currentContract->Player ) &&
            $currentContract->Type->get() != BidType::Pass->bitMaskValue()
        ) {
            if ( $currentContract->Type->has( BidType::Double ) ) {
                $availableBids->set( BidType::ReDouble->value(), new Bid( $currentPlayer, BidType::ReDouble ) );
            } else if ( $currentContract->Type->has( BidType::ReDouble ) ) {
            
            } else {
                $availableBids->set( BidType::Double->value(), new Bid( $currentPlayer, BidType::Double ) );
            }
        }
        
        return $availableBids;
    }
}
