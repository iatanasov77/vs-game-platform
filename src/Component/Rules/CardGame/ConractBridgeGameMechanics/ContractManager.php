<?php namespace App\Component\Rules\CardGame\ConractBridgeGameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;

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
        $this->game->CurrentContract = new Bid( $this->game->CurrentPlayer, BidTrump::Pass );
        $this->game->AvailableBids = $this->GetAvailableBids( $this->game->CurrentContract, $this->game->CurrentPlayer );
    }
    
    public function SetContract( Bid $bid, PlayerPosition $nextPlayer ): void
    {
        $this->game->Bids[$bid->Player->value] = $bid;
        
        if ( $bid->Trump->has( BidTrump::Double ) || $bid->Trump->has( BidTrump::ReDouble ) ) {
            $this->game->CurrentContract->Trump->remove( BidTrump::Double );
            $this->game->CurrentContract->Trump->remove( BidTrump::ReDouble );
            $this->game->CurrentContract->Trump->set( BidTrump::fromBitMaskValue( $bid->Trump->get() ) );
            
            if ( $bid->Trump->has( BidTrump::ReDouble ) ) {
                $this->game->CurrentContract->ReKontraPlayer = $this->game->CurrentPlayer;
            } else {
                $this->game->CurrentContract->KontraPlayer = $this->game->CurrentPlayer;
            }
            
            $this->logger->log( 'ConsecutivePasses After Kontra: ' . $this->game->ConsecutivePasses, 'RoundManager' );
            $this->logger->log( 'After Kontra Has Bid Pass: ' . $bid->Trump->has( BidTrump::Pass ), 'RoundManager' );
        } else if ( ! $bid->Trump->has( BidTrump::Pass ) ) {
            $this->game->CurrentContract = $bid;
        }
        
        $this->game->ConsecutivePasses = $bid->Trump->has( BidTrump::Pass ) ? ++$this->game->ConsecutivePasses : 0;
        $this->game->AvailableBids = $this->GetAvailableBids( $this->game->CurrentContract, $nextPlayer );
        
        //$this->logger->log( 'AvailableBids: ' . \print_r( $this->game->AvailableBids->toArray(), true ), 'RoundManager' );
    }
    
    private function GetAvailableBids( ?Bid $currentContract, PlayerPosition $currentPlayer ): Collection
    {
        $cleanContract = $currentContract ? $currentContract->Trump : EnumBitMask::create( BidTrump::class, BidTrump::Pass );
        
        $cleanContract->remove( BidTrump::Double );
        $cleanContract->remove( BidTrump::ReDouble );
        
        $availableBids = new ArrayCollection();
        $availableBids->set( BidTrump::Pass->value(), new Bid( $currentPlayer, BidTrump::Pass ) );
        
        if ( $cleanContract->get() < BidTrump::Clubs->bitMaskValue() ) {
            $availableBids->set( BidTrump::Clubs->value(), new Bid( $currentPlayer, BidTrump::Clubs ) );
        }
        
        if ( $cleanContract->get() < BidTrump::Diamonds->bitMaskValue() ) {
            $availableBids->set( BidTrump::Diamonds->value(), new Bid( $currentPlayer, BidTrump::Diamonds ) );
        }
        
        if ( $cleanContract->get() < BidTrump::Hearts->bitMaskValue() ) {
            $availableBids->set( BidTrump::Hearts->value(), new Bid( $currentPlayer, BidTrump::Hearts ) );
        }
        
        if ( $cleanContract->get() < BidTrump::Spades->bitMaskValue() ) {
            $availableBids->set( BidTrump::Spades->value(), new Bid( $currentPlayer, BidTrump::Spades ) );
        }
        
        if ( $cleanContract->get() < BidTrump::NoTrumps->bitMaskValue() ) {
            $availableBids->set( BidTrump::NoTrumps->value(), new Bid( $currentPlayer, BidTrump::NoTrumps ) );
        }
        
        if (
            $currentContract &&
            ! PlayerPositionExtensions::IsInSameTeamWith( $currentPlayer, $currentContract->Player ) &&
            $currentContract->Trump->get() != BidTrump::Pass->bitMaskValue()
        ) {
            if ( $currentContract->Trump->has( BidTrump::Double ) ) {
                $availableBids->set( BidTrump::ReDouble->value(), new Bid( $currentPlayer, BidTrump::ReDouble ) );
            } else if ( $currentContract->Trump->has( BidTrump::ReDouble ) ) {
                
            } else {
                $availableBids->set( BidTrump::Double->value(), new Bid( $currentPlayer, BidTrump::Double ) );
            }
        }
        
        $this->logger->log( 'Current Contract: ' . $cleanContract->get(), 'ContractManager' );
        
        return $availableBids;
    }
}
