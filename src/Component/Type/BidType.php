<?php namespace App\Component\Type;

enum BidType implements BidTypeInterface
{
    case Pass;
    case Clubs;    // ♣
    case Diamonds; // ♦
    case Hearts;   // ♥
    case Spades;   // ♠
    
    case NoTrumps;
    case AllTrumps;
    case Double;
    case ReDouble;
    
    public function color(): string
    {
        return match( $this ) {
            BidType::Hearts, BidType::Diamonds => 'Red',
            BidType::Clubs, BidType::Spades => 'Black',
        };
    }
    
    public function value(): int
    {
        return match( $this ) {
            BidType::Pass       => 0,
            BidType::Clubs      => 1,
            BidType::Diamonds   => 2,
            BidType::Hearts     => 3,
            BidType::Spades     => 4,
            
            BidType::NoTrumps   => 5,
            BidType::AllTrumps  => 6,
            BidType::Double     => 7,
            BidType::ReDouble   => 8,
        };
    }
    
    public function bitMaskValue(): int
    {
        return match( $this ) {
            BidType::Pass       => 1,
            BidType::Clubs      => 2,
            BidType::Diamonds   => 4,
            BidType::Hearts     => 8,
            BidType::Spades     => 16,
            
            BidType::NoTrumps   => 32,
            BidType::AllTrumps  => 64,
            BidType::Double     => 128,
            BidType::ReDouble   => 256,
        };
    }
    
    public static function fromValue( int $value ): self
    {
        return match( true ) {
            $value == 1 => BidType::Clubs,
            $value == 2 => BidType::Diamonds,
            $value == 3 => BidType::Hearts,
            $value == 4 => BidType::Spades,
            $value == 5 => BidType::NoTrumps,
            $value == 6 => BidType::AllTrumps,
            $value == 7 => BidType::Double,
            $value == 8 => BidType::ReDouble,
            default => BidType::Pass,
        };
    }
    
    public static function fromBitMaskValue( int $value ): self
    {
        return match( true ) {
            $value == 2 => BidType::Clubs,
            $value == 4 => BidType::Diamonds,
            $value == 8 => BidType::Hearts,
            $value == 16 => BidType::Spades,
            $value == 32 => BidType::NoTrumps,
            $value == 64 => BidType::AllTrumps,
            $value == 128 => BidType::Double,
            $value == 256 => BidType::ReDouble,
            default => BidType::Pass,
        };
    }
}
