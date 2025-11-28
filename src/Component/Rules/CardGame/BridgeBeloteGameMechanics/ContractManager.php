<?php namespace App\Component\Rules\CardGame\BridgeBeloteGameMechanics;

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
    
    public function SetContract( Bid $bid, PlayerPosition $nextPlayer ): void
    {
        $this->game->Bids[$bid->Player->value] = $bid;
        
        if ( $bid->Type->has( BidType::Double ) || $bid->Type->has( BidType::ReDouble ) ) {
            $this->game->CurrentContract->Type->remove( BidType::Double );
            $this->game->CurrentContract->Type->remove( BidType::ReDouble );
            $this->game->CurrentContract->Type->set( BidType::fromBitMaskValue( $bid->Type->get() ) );
            
            if ( $bid->Type->has( BidType::ReDouble ) ) {
                $this->game->CurrentContract->ReKontraPlayer = $this->game->CurrentPlayer;
            } else {
                $this->game->CurrentContract->KontraPlayer = $this->game->CurrentPlayer;
            }
            
            $this->logger->log( 'ConsecutivePasses After Kontra: ' . $this->game->ConsecutivePasses, 'RoundManager' );
            $this->logger->log( 'After Kontra Has Bid Pass: ' . $bid->Type->has( BidType::Pass ), 'RoundManager' );
        } else if ( ! $bid->Type->has( BidType::Pass ) ) {
            $this->game->CurrentContract = $bid;
        }
        
        $this->game->ConsecutivePasses = $bid->Type->has( BidType::Pass ) ? ++$this->game->ConsecutivePasses : 0;
        $this->game->AvailableBids = $this->GetAvailableBids( $this->game->CurrentContract, $nextPlayer );
        
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
        
        $this->logger->log( 'Current Contract: ' . $cleanContract->get(), 'ContractManager' );
        
        return $availableBids;
    }
}
