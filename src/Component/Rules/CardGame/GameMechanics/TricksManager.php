<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Rules\CardGame\Game;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\Context\PlayerGetAnnouncesContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Rules\CardGame\Player;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\PlayCardAction;

class TricksManager
{
    /** @var Game */
    private Game $game;
    
    /** @var GameLogger */
    private  $logger;
    
    /** @var TrickWinnerService */
    private $trickWinnerService;
    
    /** @var ValidCardsService */
    private $validCardsService;
    
    /** @var ValidAnnouncesService */
    private $validAnnouncesService;
    
    /** @var Collection | PlayCardAction[] */
    private $TrickActions;
    
    /** @var int */
    private $TrickNumber = 1;
    
    public function __construct( Game $game, GameLogger $logger )
    {
        $this->game = $game;
        $this->logger = $logger;
        
        $this->trickWinnerService = new TrickWinnerService();
        $this->validCardsService = new ValidCardsService();
        $this->validAnnouncesService = new ValidAnnouncesService();
        
        $this->TrickActions = new ArrayCollection();
    }
    
    public function GetValidCards( Collection $playerCards, Bid $currentContract, Collection $trickActions ): Collection
    {
        return $this->validCardsService->GetValidCards(
            $playerCards,
            $currentContract->Type,
            $trickActions
        );
    }
    
    public function GetAvailableAnnounces( Collection $playerCards ): Collection
    {
        return $this->validAnnouncesService->GetAvailableAnnounces( $playerCards );
    }
    
    public function IsBeloteAllowed( Collection $playerCards, EnumBitMask $contract, Collection $currentTrickActions, Card $playedCard ): bool
    {
        return $this->validAnnouncesService->IsBeloteAllowed( $playerCards, $contract, $currentTrickActions, $playedCard );
    }
    
    public function GetTrickNumber(): int
    {
        return $this->TrickNumber;
    }
    
    public function GetTrickActionNumber(): int
    {
        return $this->TrickActions->count();
    }
    
    public function GetTrickActions(): Collection
    {
        return $this->TrickActions;
    }
    
    public function AddTrickAction( PlayCardAction $action ): void
    {
        $this->TrickActions[] = $action;
    }
    
    /**
     * May Be Uneeded Method
     * Later Can Be Uses For Make and Player Announces
     */
    public function PlayTricks(
        int $roundNumber,
        PlayerPosition $firstToPlay,
        int $southNorthPoints,
        int $eastWestPoints,
        Collection $playerCards,
        Collection $bids,
        Bid $currentContract,
        Collection $announces,
        Collection $southNorthTricks,
        Collection $eastWestTricks,
        PlayerPosition $lastTrickWinner
    ): void {
        $announceContext = new PlayerGetAnnouncesContext();
        $announceContext->RoundNumber           = $roundNumber;
        $announceContext->EastWestPoints        = $eastWestPoints;
        $announceContext->SouthNorthPoints      = $southNorthPoints;
        $announceContext->FirstToPlayInTheRound = $firstToPlay;
        $announceContext->Bids                  = $bids;
        $announceContext->CurrentContract       = $currentContract;
        $announceContext->CurrentTrickActions   = $trickActions;
        $announceContext->Announces             = $announces;
        
        $playContext = new PlayerPlayCardContext();
        $playContext->RoundNumber           = $roundNumber;
        $playContext->EastWestPoints        = $eastWestPoints;
        $playContext->SouthNorthPoints      = $southNorthPoints;
        $playContext->FirstToPlayInTheRound = $firstToPlay;
        $playContext->CurrentContract       = $currentContract;
        $playContext->Bids                  = $bids;
        $playContext->Announces             = $announces;
        $playContext->RoundActions          = $actions;
        $playContext->CurrentTrickActions   = $trickActions;
        
        // Announces
        if ( $this->TrickNumber == 1 && ! $currentContract->Type->has( BidType::NoTrumps ) ) {
            // Prepare GetAnnounces context
            $availableAnnounces = $this->validAnnouncesService->GetAvailableAnnounces( $this->game->playerCards[$this->game->CurrentPlayer->value] );
            if ( $availableAnnounces->count() > 0 ) {
                $announceContext->MyPosition = $this->game->CurrentPlayer;
                $announceContext->MyCards = $this->game->playerCards[$this->game->CurrentPlayer->value];
                $announceContext->AvailableAnnounces = $availableAnnounces;
                
                // Execute GetAnnounces
                $playerAnnounces = this.players[$this->game->CurrentPlayer->value].GetAnnounces( $announceContext );
                
                // Validate
                for ( $i = 0; $i < $playerAnnounces->count(); $i++) {
                    $playerAnnounce = $playerAnnounces[$i];
                    $availableAnnounce = $availableAnnounces->filter(
                        function( $entry ) {
                            return $entry->Type == $playerAnnounce->Type && $entry->Card == $playerAnnounce->Card;
                        }
                    )->first();
                    if ( $availableAnnounce == null ) {
                        // Invalid announce
                        continue;
                    }
                    
                    $availableAnnounces->removeElement( $availableAnnounce );
                    
                    $playerAnnounce->Player = $this->game->CurrentPlayer;
                    $announces[] = $playerAnnounce;
                }
            }
        }
        
        // Prepare PlayCard context
        $availableCards = $this->validCardsService->GetValidCards(
            $this->game->playerCards[$this->game->CurrentPlayer->value],
            $currentContract->Type,
            $trickActions
        );
        
        if ( $availableCards->count() == 1 ) {
            // Only 1 card is available. Play it. Belot is not available in this situation.
            $action = new PlayCardAction( $availableCards->first(), false );
        } else {
            $playContext->MyPosition = $this->game->CurrentPlayer;
            $playContext->MyCards = $this->game->playerCards[$this->game->CurrentPlayer->value];
            $playContext->AvailableCardsToPlay = $availableCards;
            
            // Execute PlayCard
            // PlayCardAction action = this.players[currentPlayerIndex].PlayCard(playContext);
            
            // Validate
            if ( ! $availableCards->contains( $action->Card ) ) {
                throw new BelotGameException( "Invalid card played from {$currentPlayer->value} player." );
            }
            
            // Belote
            if ( $action->Belote ) {
                if ( $this->validAnnouncesService->IsBeloteAllowed(
                        $this->game->playerCards[$this->game->CurrentPlayer->value],
                        $currentContract->Type,
                        $trickActions,
                        $action->Card
                    )
                ) {
                    $announces[] = new Announce( AnnounceType::Belot, $action->Card );
                } else {
                    $action->Belote = false;
                }
            }
        }
        
        // Update information after the action
        $this->game->playerCards[$this->game->CurrentPlayer->value]->remove( $action->Card );
        $action->Player = currentPlayer;
        $action->TrickNumber = trickNumber;
        $actions[] = $action;
        $this->TrickActions[] = $action;
        
        // Next player
        $currentPlayer = $currentPlayer->Next();
    }
    
    public function GetTricksWinner(): PlayerPosition
    {
        $winner = $this->trickWinnerService->GetWinner( $this->game->CurrentContract, $this->TrickActions );
        if ( $winner == PlayerPosition::South || $winner == PlayerPosition::North ) {
            $this->game->SouthNorthTricks[] = $this->TrickActions[0]->Card;
            $this->game->SouthNorthTricks[] = $this->TrickActions[1]->Card;
            $this->game->SouthNorthTricks[] = $this->TrickActions[2]->Card;
            $this->game->SouthNorthTricks[] = $this->TrickActions[3]->Card;
        } else if ( $winner == PlayerPosition::East || $winner == PlayerPosition::West ) {
            $this->game->EastWestTricks[] = $this->TrickActions[0]->Card;
            $this->game->EastWestTricks[] = $this->TrickActions[1]->Card;
            $this->game->EastWestTricks[] = $this->TrickActions[2]->Card;
            $this->game->EastWestTricks[] = $this->TrickActions[3]->Card;
        }
        
        if ( $this->TrickNumber == 8 ) {
            $this->game->LastTrickWinner = $winner;
        }
        
        // The player that wins the trick plays first
        $this->game->CurrentPlayer = $winner;
        $this->TrickActions = new ArrayCollection();
        
        return $winner;
    }
}
