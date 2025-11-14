<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Rules\CardGame\Game;

use App\Component\Type\GameState;
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
        $this->validAnnouncesService = new ValidAnnouncesService( $this->logger );
        
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
    
    public function GetTricksWinner(): PlayerPosition
    {
        if ( $this->TrickNumber == 2 ) {
            $this->validAnnouncesService->UpdateActiveAnnounces( $this->game->announces );
        }
        
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
        
        if ( $this->TrickNumber > 8 ) {
            $this->game->LastTrickWinner = $winner;
        }
        
        if ( $this->game->PlayState == GameState::playing ) {
            // The player that wins the trick plays first
            $this->game->CurrentPlayer = $winner;
            $this->TrickActions = new ArrayCollection();
        }
        
        return $winner;
    }
}
