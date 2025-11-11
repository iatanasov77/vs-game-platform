<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\AnnounceType;
use App\Component\Type\PlayerPosition;

class Announce
{
    public AnnounceType $Type;
    
    public PlayerPosition $Player;
    
    public Card $Card;
    
    public ?bool $IsActive;
    
    public function __construct( AnnounceType $type, Card $card )
    {
        $this->Type = $type;
        $this->Card = $card;
    }
    
    public function Value(): int
    {
        switch ( $this->Type ) {
            case AnnounceType::Belot:
                return 20;
                break;
            case AnnounceType::SequenceOf3:
                return 20;
                break;
            case AnnounceType::SequenceOf4:
                return 50;
                break;
            case AnnounceType::SequenceOf5:
                return 100;
                break;
            case AnnounceType::SequenceOf6:
                return 100;
                break;
            case AnnounceType::SequenceOf7:
                return 100;
                break;
            case AnnounceType::SequenceOf8:
                return 100;
                break;
            case AnnounceType::FourOfAKind:
                return 100;
                break;
            case AnnounceType::FourNines:
                return 150;
                break;
            case AnnounceType::FourJacks:
                return 200;
                break;
        }
        
        return 0;
    }
    
    public function __toString(): string
    {
        switch ( $this->Type ) {
            case AnnounceType::Belot:
                return "Belot {$this->Card->Suit->ToFriendlyString()}";
                break;
            case AnnounceType::FourJacks:
                return "4 Jacks";
                break;
            case AnnounceType::FourNines:
                return "4 Nines";
                break;
            case AnnounceType::FourOfAKind:
                return "4 of a kind {$this->Card->Type->toString()}";
                break;
            case AnnounceType::SequenceOf8:
                return "Quinte(8) to {$this->Card}";
                break;
            case AnnounceType::SequenceOf7:
                return "Quinte(7) to {$this->Card}";
                break;
            case AnnounceType::SequenceOf6:
                return "Quinte(6) to {$this->Card}";
                break;
            case AnnounceType::SequenceOf5:
                return "Quinte to {$this->Card}";
                break;
            case AnnounceType::SequenceOf4:
                return "Quarte to {$this->Card}";
                break;
            case AnnounceType::SequenceOf3:
                return "Tierce to {$this->Card}";
                break;
            default:
                throw new BelotGameException( "Invalid announce type {$this->Type->toString()} ({$this->Type->value})" );
        }
    }
    
    public function CompareTo( ?Announce $other ): int
    {
        if ( $this == $other ) {
            return 0;
        }
        
        if ( $other == null ) {
            return 1;
        }
        
        if ( $this->Value > $other->Value ) {
            return 1;
        }
        
        if ( $other->Value > $this->Value ) {
            return -1;
        }
        
        if ( $this->Type > $other->Type ) {
            return 1;
        }
        
        if ( $other->Type > $this->Type ) {
            return -1;
        }
        
        return $this->Card->Type->CompareTo( $other->Card->Type );
    }
}
