<?php namespace App\Component\Rules\CardGame\GameMechanics;

use BitMask\EnumBitMask;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\BidType;
use App\Component\Type\CardSuit;
use App\Component\Type\CardType;
use App\Component\Type\AnnounceType;
use App\Component\Rules\CardGame\Helper;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Component\Rules\CardGame\BidTypeExtensions;

class ValidAnnouncesService
{
    use Helper;
    
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( GameLogger $logger )
    {
        $this->logger = $logger;
    }
    
    public function IsBeloteAllowed( Collection $playerCards, EnumBitMask $contract, Collection $currentTrickActions, Card $playedCard ): bool
    {
        if ( $playedCard->Type != CardType::Queen && $playedCard->Type != CardType::King ) {
            return false;
        }
        
        if ( $contract->has( BidType::NoTrumps ) ) {
            return false;
        }
        
        if ( $contract->has( BidType::AllTrumps ) ) {
            if ( $currentTrickActions->count() > 0 && $currentTrickActions[0]->Card->Suit != $playedCard->Suit ) {
                // Belote is only allowed when playing card from the same suit as the first card played
                return false;
            }
        } else {
            $suit = BidType::fromBitMaskValue( $contract->get() );
            // Clubs, Diamonds, Hearts or Spades
            if ( $playedCard->Suit != BidTypeExtensions::ToCardSuit( $suit ) ) {
                // Belote is only allowed when playing card from the trump suit
                return false;
            }
        }
        
        return $playerCards->contains(
            $playedCard->Type == CardType::Queen
                ? Card::GetCard( $playedCard->Suit, CardType::King )
                : Card::GetCard( $playedCard->Suit, CardType::Queen )
        );
    }
    
    public function GetAvailableAnnounces( Collection $playerCards ): Collection
    {
        $cards = new ArrayCollection( $playerCards->toArray() );
        $combinations = new ArrayCollection();
        $this->FindFourOfAKindAnnounces( $cards, $combinations );
        $this->FindSequentialAnnounces( $cards, $combinations );
        
        return $combinations;
    }
    
    public function UpdateActiveAnnounces( Collection $announces ): void
    {
        $maxSameTypesAnnounce = null;
        $maxSameSuitAnnounce = null;
        foreach ( $announces as $announce ) {
            if ( $announce->Type == AnnounceType::Belot ) {
                
            } else if (
                $announce->Type == AnnounceType::FourJacks
                || $announce->Type == AnnounceType::FourNines
                || $announce->Type == AnnounceType::FourOfAKind
            ) {
                if ( announce.CompareTo(maxSameTypesAnnounce) > 0 ) {
                    $maxSameTypesAnnounce = $announce;
                }
            } else {
                // Sequence
                if ( $announce->CompareTo( $maxSameSuitAnnounce ) > 0 ) {
                    $maxSameSuitAnnounce = announce;
                }
            }
        }
        
        // Check for same announces in different teams
        $sameMaxAnnounceInDifferentTeams = false;
        foreach ( $announces as $announce ) {
            if (
                $announce->Type == AnnounceType::SequenceOf3
                || $announce->Type == AnnounceType::SequenceOf4
                || $announce->Type == AnnounceType::SequenceOf5
                || $announce->Type == AnnounceType::SequenceOf6
                || $announce->Type == AnnounceType::SequenceOf7
                || $announce->Type == AnnounceType::SequenceOf8
            ) {
                if (
                    $announce.CompareTo( $maxSameSuitAnnounce ) == 0
                    && $maxSameSuitAnnounce != null
                    && ! PlayerPositionExtensions::IsInSameTeamWith( $announce->Player, $maxSameSuitAnnounce->Playern )
                ) {
                    $sameMaxAnnounceInDifferentTeams = true;
                }
            }
        }
        
        // Mark announces that should be scored
        foreach ( $announces as $announce ) {
            $announce->IsActive = false;
            if ( $announce->Type == AnnounceType::Belot ) {
                $announce->IsActive = true;
            } elseif (
                $announce->Type == AnnounceType::FourJacks
                || $announce->Type == AnnounceType::FourNines
                || $announce->Type == AnnounceType::FourOfAKind
            ) {
                if (
                    $announce->CompareTo( $maxSameTypesAnnounce ) >= 0 ||
                    ( $maxSameTypesAnnounce != null && PlayerPositionExtensions::IsInSameTeamWith( $announce->Player, $maxSameTypesAnnounce->Player ) )
                ) {
                    $announce->IsActive = true;
                }
            } elseif ( ! $sameMaxAnnounceInDifferentTeams ) {
                // Sequence
                if (
                    $announce->CompareTo( $maxSameSuitAnnounce ) >= 0 ||
                    ( $maxSameSuitAnnounce != null && PlayerPositionExtensions::IsInSameTeamWith( $announce->Player, $maxSameSuitAnnounce->Playern ) )
                ) {
                    $announce->IsActive = true;
                }
            }
        }
    }
    
    private function FindFourOfAKindAnnounces( Collection $cards, Collection &$combinations ): void
    {
        // Group by type
        $countOfCardTypes = [
            CardType::Seven->value => 0,
            CardType::Eight->value => 0,
            CardType::Nine->value => 0,
            CardType::Ten->value => 0,
            CardType::Jack->value => 0,
            CardType::Queen->value => 0,
            CardType::King->value => 0,
            CardType::Ace->value => 0,
        ];
        foreach ( $cards as $card ) {
            $countOfCardTypes[$card->Type->value]++;
        }
        
        // Check each type
        for ( $i = 0; $i < 8; $i++ ) {
            $cardType = CardType::from( $i );
            if ( $countOfCardTypes[$i] != 4 || $cardType == CardType::Seven || $cardType == CardType::Eight) {
                continue;
            }
            
            if ( $cardType == CardType::Jack ) {
                $combinations[] = new Announce( AnnounceType::FourJacks, Card::GetCard( CardSuit::Spade, $cardType ) );
            } elseif ( $cardType == CardType::Nine ) {
                $combinations[] = new Announce( AnnounceType::FourNines, Card::GetCard( CardSuit::Spade, $cardType ) );
            } elseif (
                $cardType == CardType::Ace ||
                $cardType == CardType::King ||
                $cardType == CardType::Queen ||
                $cardType == CardType::Ten
            ) {
                $combinations[] = new Announce( AnnounceType::FourOfAKind, Card::GetCard( CardSuit::Spade, $cardType ) );
            }
            
            // Remove these cards from the available combination cards
            foreach ( $cards as $card ) {
                if ( $card->Type == $cardType ) {
                    $cards->removeElement( $card );
                }
            }
        }
    }
    
    private function FindSequentialAnnounces( Collection $cards, Collection &$combinations ): void
    {
        // Group by suit
        $cardsBySuit = [
            CardSuit::Club->value       => new ArrayCollection(),
            CardSuit::Diamond->value    => new ArrayCollection(),
            CardSuit::Heart->value      => new ArrayCollection(),
            CardSuit::Spade->value      => new ArrayCollection(),
        ];
        foreach ( $cards as $card ) {
            $cardsBySuit[$card->Suit->value][] = $card;
        }
        
        // Check each suit
        for ( $suitIndex = 0; $suitIndex < 4; $suitIndex++ ) {
            if ( $cardsBySuit[$suitIndex]->count() < 3 ) {
                continue;
            }
            
            $suitedCards = $this->sortCards( $cardsBySuit[$suitIndex] );
            $previousCardValue = $suitedCards[0]->Type->value;
            $count = 1;
            for ( $i = 1; $i < $suitedCards->count(); $i++ ) {
                if ( $suitedCards[$i]->Type->value == $previousCardValue + 1 ) {
                    $count++;
                } else {
                    if ( $count == 3 ) {
                        $combinations[] = new Announce( AnnounceType::SequenceOf3, $suitedCards[$i - 1] );
                    } elseif ( $count == 4 ) {
                        $combinations[] = new Announce( AnnounceType::SequenceOf4, $suitedCards[$i - 1] );
                    } elseif ( $count == 5 ) {
                        $combinations[] = new Announce( AnnounceType::SequenceOf5, $suitedCards[$i - 1] );
                    } elseif ( $count == 6 ) {
                        $combinations[] = new Announce( AnnounceType::SequenceOf6, $suitedCards[$i - 1] );
                    }
                    
                    $count = 1;
                }
                
                $previousCardValue = $suitedCards[$i]->Type->value;
            }
            
            if ( $count == 3 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf3, $suitedCards[$suitedCards->count() - 1] );
            } elseif ( $count == 4 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf4, $suitedCards[$suitedCards->count() - 1] );
            } elseif ( $count == 5 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf5, $suitedCards[$suitedCards->count() - 1] );
            } elseif ( $count == 6 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf6, $suitedCards[$suitedCards->count() - 1] );
            } elseif ( $count == 7 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf7, $suitedCards[$suitedCards->count() - 1] );
            } elseif ( $count == 8 ) {
                $combinations[] = new Announce( AnnounceType::SequenceOf8, $suitedCards[$suitedCards->count() - 1] );
                $combinations[] = new Announce( AnnounceType::SequenceOf3, $suitedCards[$suitedCards->count() - 1] );
            }
        }
    }
}
