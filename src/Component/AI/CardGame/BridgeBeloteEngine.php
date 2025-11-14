<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\Type\BidType;
use App\Component\Type\AnnounceType;
use App\Component\Type\CardType;
use App\Component\Type\CardSuit;
use App\Component\GameLogger;
use App\Component\Type\PlayerPosition;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\GameMechanics\ValidCardsService;
use App\Component\Rules\CardGame\GameMechanics\ValidAnnouncesService;
use App\Component\Rules\CardGame\GameMechanics\TrickWinnerService;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayerPositionExtensions;

// Contexts
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;

// Strategies
use App\Component\AI\CardGame\Strategies\IPlayStrategy;
use App\Component\AI\CardGame\Strategies\AllTrumpsOursContractStrategy;
use App\Component\AI\CardGame\Strategies\AllTrumpsTheirsContractStrategy;
use App\Component\AI\CardGame\Strategies\NoTrumpsOursContractStrategy;
use App\Component\AI\CardGame\Strategies\NoTrumpsTheirsContractStrategy;
use App\Component\AI\CardGame\Strategies\TrumpOursContractStrategy;
use App\Component\AI\CardGame\Strategies\TrumpTheirsContractStrategy;

/**
 * BelotGameEngine in C#: https://github.com/NikolayIT/BelotGameEngine
 */
class BridgeBeloteEngine extends Engine
{
    /** @var ValidCardsService */
    private $validCardsService;
    
    private ValidAnnouncesService $validAnnouncesService;
    private TrickWinnerService $trickWinnerService;
    
    private IPlayStrategy $allTrumpsOursContractStrategy;
    private IPlayStrategy $allTrumpsTheirsContractStrategy;
    private IPlayStrategy $noTrumpsOursContractStrategy;
    private IPlayStrategy $noTrumpsTheirsContractStrategy;
    private IPlayStrategy $trumpOursContractStrategy;
    private IPlayStrategy $trumpTheirsContractStrategy;
    
    public function __construct( GameLogger $logger, Game $game )
    {
        parent::__construct( $logger, $game );
        
        $this->validCardsService = new ValidCardsService();
        $this->validAnnouncesService = new ValidAnnouncesService( $this->logger );
        $this->trickWinnerService = new TrickWinnerService();
        
        $this->allTrumpsOursContractStrategy = new AllTrumpsOursContractStrategy();
        $this->allTrumpsTheirsContractStrategy = new AllTrumpsTheirsContractStrategy();
        $this->noTrumpsOursContractStrategy = new NoTrumpsOursContractStrategy();
        $this->noTrumpsTheirsContractStrategy = new NoTrumpsTheirsContractStrategy();
        $this->trumpOursContractStrategy = new TrumpOursContractStrategy();
        $this->trumpTheirsContractStrategy = new TrumpTheirsContractStrategy();
    }
    
    public function DoBid(): BidType
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
        $availableCards = $this->validCardsService->GetValidCards(
            $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value],
            $this->EngineGame->CurrentContract->Type,
            $this->EngineGame->GetTrickActions()
        );
        
        $context = new PlayerPlayCardContext();
        $context->MyPosition = $this->EngineGame->CurrentPlayer;
        $context->Bids = $this->EngineGame->Bids;
        $context->CurrentContract = $this->EngineGame->CurrentContract;
        $context->MyCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
        $context->Announces = $this->EngineGame->GetAvailableAnnounces( $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value] );
        $context->CurrentTrickActions = $this->EngineGame->GetTrickActions();
        $context->RoundActions = $this->EngineGame->GetTrickActions();
        $context->AvailableCardsToPlay = $availableCards;
        $context->CurrentTrickNumber = $this->EngineGame->trickNumber;
        
        $action = $this->_PlayCard( $context );
        
        // Belote
        if ( $action->Belote ) {
            if ( $this->EngineGame->IsBeloteAllowed(
                $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value],
                $this->EngineGame->CurrentContract->Type,
                $this->EngineGame->GetTrickActions(),
                $action->Card
            )
            ) {
                $announces[] = new Announce( AnnounceType::Belot, $action->Card );
            } else {
                $action->Belote = false;
            }
        }
        
        // Update information after the action
        $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value]->removeElement( $action->Card );
        $action->Player = $this->EngineGame->CurrentPlayer;
        $action->TrickNumber = $this->EngineGame->GetTrickActionNumber() + 1;
        
        return $action;
    }
    
    protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void
    {
        
    }
    
    private function GetBid( PlayerGetBidContext $context ): BidType
    {
        $availableAnnounces = $this->validAnnouncesService->GetAvailableAnnounces( $context->MyCards );
        //$this->logger->log( 'Available Announces for Player ' . $context->MyPosition->value . ': ' . \print_r( $availableAnnounces->toArray(), true ), 'BridgeBeloteEngine' );
        
        $announcePoints = \array_reduce(
            $availableAnnounces->toArray(),
            function( $carry, $item )
            {
                return $carry + $item->Value();
            }
        );
        
        if ( ! $announcePoints ) {
            $announcePoints = 0;
        }
        
        $bids = new ArrayCollection();
        
        if ( $context->AvailableBids->containsKey( BidType::Clubs->value() ) ) {
            $bids->set(
                BidType::Clubs->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Club, $announcePoints )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidType::Diamonds->value() ) ) {
            $bids->set(
                BidType::Diamonds->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Diamond, $announcePoints )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidType::Hearts->value() ) ) {
            $bids->set(
                BidType::Hearts->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Heart, $announcePoints )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidType::Spades->value() ) ) {
            $bids->set(
                BidType::Spades->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Spade, $announcePoints )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidType::AllTrumps->value() ) ) {
            $teammate = PlayerPositionExtensions::GetTeammate( $context->MyPosition );
            $bids->set(
                BidType::AllTrumps->value(),
                self::CalculateAllTrumpsBidPoints( $context->MyCards, $context->Bids, $teammate, $announcePoints )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidType::NoTrumps->value() ) ) {
            $bids->set(
                BidType::NoTrumps->value(),
                self::CalculateNoTrumpsBidPoints( $context->MyCards )
            );
        }
        
        $this->logger->log( 'Bids Before Filter for Player ' . $context->MyPosition->value . ': ' . \print_r( $bids->toArray(), true ), 'BridgeBeloteEngine' );
        $bids = $bids->filter(
            function( $entry ) {
                return $entry >= 100;
            }
        );
        $this->logger->log( 'Bids After Filter for Player ' . $context->MyPosition->value . ': ' . \print_r( $bids->toArray(), true ), 'BridgeBeloteEngine' );
        
        $bidsIterator = $bids->getIterator();
        $bidsIterator->uasort( function ( $a, $b ) {
            return $b <=> $a;
        });
        $bids = new ArrayCollection( \iterator_to_array( $bidsIterator ) );
        $bid = $bids->first() ? BidType::fromValue( $bids->key() ) : BidType::Pass;
        
        //$this->logger->log( 'Available Bids for Player ' . $context->MyPosition->value . ': ' . \print_r( $context->AvailableBids->toArray(), true ), 'BridgeBeloteEngine' );
        $this->logger->log( 'Selected Bid for Player ' . $context->MyPosition->value . ': ' . \print_r( $bid, true ), 'BridgeBeloteEngine' );
        
        return $bid;
    }
    
    private function _PlayCard( PlayerPlayCardContext $context ): PlayCardAction
    {
        $playedCards = new ArrayCollection();
        foreach ( $context->RoundActions as $action ) {
            if ( $action->TrickNumber < $context->CurrentTrickNumber ) {
                $playedCards[] = $action->Card;
            }
        }
        
        if ( $context->CurrentContract->Type->has( BidType::AllTrumps ) ) {
            $strategy = PlayerPositionExtensions::IsInSameTeamWith( $context->CurrentContract->Player, $context->MyPosition )
                            ? $this->allTrumpsOursContractStrategy
                            : $this->allTrumpsTheirsContractStrategy;
        } else if ( $context->CurrentContract->Type->has( BidType::NoTrumps ) ) {
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
    }
    
    private static function CalculateAllTrumpsBidPoints(
        Collection $cards,
        Collection $previousBids,
        PlayerPosition $teammate,
        int $announcePoints
    ): int {
        $bidPoints = $announcePoints / 3;
        foreach ( $cards as $card ) {
            if ( $card->Type == CardType::Jack ) {
                $bidPoints += 45;
            }
            
            if ( $card->Type == CardType::Nine ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Jack ) ) ? 25 : 15;
            }
            
            if ( $card->Type == CardType::Ace ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                                && $cards->contains( Card::GetCard( $card->Suit, CardType::Nine ) )
                                    ? 10
                                    : 5;
            }
        }
        
        $teammateHasSuitAnnounce = $previousBids->filter(
            function( $entry ) use ( $teammate ) {
                return $entry->Player == $teammate && (
                    $entry->Type == BidType::Clubs
                    || $entry->Type == BidType::Diamonds
                    || $entry->Type == BidType::Hearts
                    || $entry->Type == BidType::Spades
                );
            }
        )->count();
        if ( $teammateHasSuitAnnounce ) {
            // If the teammate has announced suit, increase all trump bid points
            $bidPoints += 5;
        }
        
        return $bidPoints;
    }
    
    private static function CalculateNoTrumpsBidPoints( Collection $cards ): int
    {
        $bidPoints = 0;
        foreach ( $cards as $card ) {
            if ( $card->Type == CardType::Ace ) {
                $bidPoints += 45;
            }
            
            if ( $card->Type == CardType::Ten ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Ace ) ) ? 25 : 15;
            }
            
            if ( $card->Type == CardType::King ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Ace ) )
                                && $cards->contains( Card::GetCard( $card->Suit, CardType::Ten ) )
                                    ? 10
                                    : 5;
            }
        }
        
        return $bidPoints;
    }
    
    private static function CalculateTrumpBidPoints( Collection $cards, CardSuit $trumpSuit, int $announcePoints ): int
    {
        $bidPoints = $announcePoints / 2;
        foreach ( $cards as $card ) {
            if ( $card->Suit == $trumpSuit ) {
                if ( $card->Type == CardType::Queen && $cards->contains( Card::GetCard( $trumpSuit, CardType::King ) ) ) {
                    $bidPoints += 25;
                } else {
                    if ( $card->Type == CardType::Jack ) {
                        $bidPoints += 55;
                    } elseif ( $card->Type == CardType::Nine ) {
                        $bidPoints += 35;
                    } elseif ( $card->Type == CardType::Ace ) {
                        $bidPoints += 25;
                    } elseif ( $card->Type == CardType::Ten ) {
                        $bidPoints += 20;
                    } elseif ( $card->Type == CardType::King || $card->Type == CardType::Queen ) {
                        $bidPoints += 16;
                    } elseif ( $card->Type == CardType::Seven || $card->Type == CardType::Eight ) {
                        $bidPoints += 15;
                    }
                }
                
            } else {
                if ( $card->Type == CardType::Ten && $cards->contains( Card::GetCard( $card->Suit, CardType::Ace ) ) ) {
                    $bidPoints += 15;
                } else {
                    if ( $card->Type == CardType::Ace ) {
                        $bidPoints += 20;
                    } elseif ( $card->Type == CardType::Ten ) {
                        $bidPoints += 10;
                    }
                }
            }
        }
        
        return $bidPoints;
    }
}
