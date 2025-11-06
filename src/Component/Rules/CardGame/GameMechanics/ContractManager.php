<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

use App\Component\GameLogger;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Bid;

class ContractManager
{
    /** @var Game */
    private Game $game;
    
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( Game $game, GameLogger $logger )
    {
        $this->game = $game;
        $this->logger = $logger;
    }
    
    public function StartNewRound(): void
    {
        $this->game->ConsecutivePasses = 0;
        $this->game->CurrentContract = new Bid( $this->game->CurrentPlayer, BidType::Pass );
        $this->game->AvailableBids = $this->GetAvailableBids( $this->game->CurrentContract, $this->game->CurrentPlayer );
    }
    
    public function SetContract( Bid $bid ): void
    {
        $this->game->Bids[$bid->Player->value] = $bid;
        
        if ( $bid->Type->has( BidType::Double ) || $bid->Type->has( BidType::ReDouble ) ) {
            $this->game->CurrentContract->Type->remove( BidType::Double );
            $this->game->CurrentContract->Type->remove( BidType::ReDouble );
            $this->game->CurrentContract->Type->set( $bid->Type );
            $this->game->CurrentContract->Player = $this->game->CurrentPlayer;
        } else if ( ! $bid->Type->has( BidType::Pass ) ) {
            $this->game->CurrentContract = $bid;
        }
        
        $this->game->ConsecutivePasses = $bid->Type->has( BidType::Pass ) ? ++$this->game->ConsecutivePasses : 0;
        $this->game->AvailableBids = $this->GetAvailableBids( $this->game->CurrentContract, $this->game->CurrentPlayer );
        
        //$this->logger->log( 'AvailableBids: ' . \print_r( $this->game->AvailableBids->toArray(), true ), 'RoundManager' );
    }
    
    private function GetAvailableBids( ?Bid $currentContract, PlayerPosition $currentPlayer ): Collection
    {
        $cleanContract = $currentContract ? $currentContract->Type : EnumBitMask::create( BidType::class, BidType::Pass );
        
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
            $currentContract &&
            ! PlayerPositionExtensions::IsInSameTeamWith( $currentPlayer, $currentContract->Player ) &&
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
