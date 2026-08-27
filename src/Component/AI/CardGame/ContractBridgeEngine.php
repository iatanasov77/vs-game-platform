<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;
use App\Component\Type\ContractBridgeCardType as CardType;
use App\Component\Rules\CardGame\ContractBridgeCard as Card;
use App\Component\Type\CardSuit;
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
        $availableCards = $this->validCardsService->GetValidCards(
            $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value],
            $this->EngineGame->CurrentContract->Trump,
            $this->EngineGame->GetTrickActions()
        );
        
        $context = new PlayerPlayCardContext();
        $context->MyPosition = $this->EngineGame->CurrentPlayer;
        $context->Bids = $this->EngineGame->Bids;
        $context->CurrentContract = $this->EngineGame->CurrentContract;
        $context->MyCards = $this->EngineGame->playerCards[$this->EngineGame->CurrentPlayer->value];
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
        //$bid = BidTrump::Pass;
        
        $bids = new ArrayCollection();
        
        if ( $context->AvailableBids->containsKey( BidTrump::Clubs->value() ) ) {
            $bids->set(
                BidTrump::Clubs->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Club )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidTrump::Diamonds->value() ) ) {
            $bids->set(
                BidTrump::Diamonds->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Diamond )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidTrump::Hearts->value() ) ) {
            $bids->set(
                BidTrump::Hearts->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Heart )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidTrump::Spades->value() ) ) {
            $bids->set(
                BidTrump::Spades->value(),
                self::CalculateTrumpBidPoints( $context->MyCards, CardSuit::Spade )
            );
        }
        
        if ( $context->AvailableBids->containsKey( BidTrump::NoTrumps->value() ) ) {
            $bids->set(
                BidTrump::NoTrumps->value(),
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
        $bid = $bids->first() ? BidTrump::fromValue( $bids->key() ) : BidTrump::Pass;
        
        //$this->logger->log( 'Available Bids for Player ' . $context->MyPosition->value . ': ' . \print_r( $context->AvailableBids->toArray(), true ), 'BridgeBeloteEngine' );
        $this->logger->log( 'Selected Bid for Player ' . $context->MyPosition->value . ': ' . \print_r( $bid, true ), 'BridgeBeloteEngine' );
        
        return $bid;
    }
    
    private function _PlayCard( PlayerPlayCardContext $context ): PlayCardAction
    {
        $this->logger->log( "Trick Actions Count: {$context->CurrentTrickActions->count()}", 'GameManager' );
        
        $playedCards = new ArrayCollection();
        foreach ( $context->RoundActions as $action ) {
            if ( $action->TrickNumber < $context->CurrentTrickNumber ) {
                $playedCards[] = $action->Card;
            }
        }
        
        if ( $context->CurrentContract->Trump->has( BidTrump::NoTrumps ) ) {
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
        PlayerPosition $teammate
    ): int {
        $bidPoints = 0;
        foreach ( $cards as $card ) {
            if ( $card->Type == CardType::Jack ) {
                $bidPoints += 45;
            }
            
            if ( $card->Type == CardType::Nine ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Jack ) ) ? 25 : 15;
            }
            
            if ( $card->Type == CardType::Ace ) {
                $bidPoints += $cards->contains( Card::GetCard( $card->Suit, CardType::Jack ) )
                && $cards->contains( Card::GetCard( $card->Suit, CardType::Nine ) ) ? 10 : 5;
            }
        }
        
        $teammateHasSuitAnnounce = $previousBids->filter(
            function( $entry ) use ( $teammate ) {
                return $entry->Player == $teammate && (
                    $entry->Trump == BidTrump::Clubs
                    || $entry->Trump == BidTrump::Diamonds
                    || $entry->Trump == BidTrump::Hearts
                    || $entry->Trump == BidTrump::Spades
                );
            }
        )->count();
        if ( $teammateHasSuitAnnounce ) {
            // If the teammate has announced suit, increase all trump bid points
            $bidPoints += 5;
        }
        
        return \intval( $bidPoints );
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
                && $cards->contains( Card::GetCard( $card->Suit, CardType::Ten ) ) ? 10 : 5;
            }
        }
        
        return \intval( $bidPoints );
    }
    
    private static function CalculateTrumpBidPoints( Collection $cards, CardSuit $trumpSuit ): int
    {
        $bidPoints = 0;
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
        
        return \intval( $bidPoints );
    }
}
