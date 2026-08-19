<?php namespace App\Component\Type;

enum BidTrump implements BidTrumpInterface
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
            BidTrump::Hearts, BidTrump::Diamonds => 'Red',
            BidTrump::Clubs, BidTrump::Spades => 'Black',
        };
    }
    
    public function value(): int
    {
        return match( $this ) {
            BidTrump::Pass       => 0,
            BidTrump::Clubs      => 1,
            BidTrump::Diamonds   => 2,
            BidTrump::Hearts     => 3,
            BidTrump::Spades     => 4,
            
            BidTrump::NoTrumps   => 5,
            BidTrump::AllTrumps  => 6,
            BidTrump::Double     => 7,
            BidTrump::ReDouble   => 8,
        };
    }
    
    public function bitMaskValue(): int
    {
        return match( $this ) {
            BidTrump::Pass       => 1,
            BidTrump::Clubs      => 2,
            BidTrump::Diamonds   => 4,
            BidTrump::Hearts     => 8,
            BidTrump::Spades     => 16,
            
            BidTrump::NoTrumps   => 32,
            BidTrump::AllTrumps  => 64,
            BidTrump::Double     => 128,
            BidTrump::ReDouble   => 256,
        };
    }
    
    public static function fromValue( int $value ): self
    {
        return match( true ) {
            $value == 1 => BidTrump::Clubs,
            $value == 2 => BidTrump::Diamonds,
            $value == 3 => BidTrump::Hearts,
            $value == 4 => BidTrump::Spades,
            $value == 5 => BidTrump::NoTrumps,
            $value == 6 => BidTrump::AllTrumps,
            $value == 7 => BidTrump::Double,
            $value == 8 => BidTrump::ReDouble,
            default => BidTrump::Pass,
        };
    }
    
    public static function fromBitMaskValue( int $value ): self
    {
        return match( true ) {
            $value == 2 => BidTrump::Clubs,
            $value == 4 => BidTrump::Diamonds,
            $value == 8 => BidTrump::Hearts,
            $value == 16 => BidTrump::Spades,
            $value == 32 => BidTrump::NoTrumps,
            $value == 64 => BidTrump::AllTrumps,
            $value == 128 => BidTrump::Double,
            $value == 256 => BidTrump::ReDouble,
            default => BidTrump::Pass,
        };
    }
}
