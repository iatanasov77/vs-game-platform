<?php namespace App\Component\Type;

enum AnnounceType: int
{
    case Belot          = 0;
    case SequenceOf3    = 1; // 20
    case SequenceOf4    = 2; // 50
    case SequenceOf5    = 3; // 100
    case SequenceOf6    = 4; // 100
    case SequenceOf7    = 5; // 100
    case SequenceOf8    = 6; // 100
    case FourOfAKind    = 7; // 100
    case FourNines      = 8; // 150
    case FourJacks      = 9; // 200
    
    public function toString(): string
    {
        return match( $this ) {
            AnnounceType::Belot => 'Belot',
            AnnounceType::SequenceOf3 => 'SequenceOf3',
            AnnounceType::SequenceOf4 => 'SequenceOf4',
            AnnounceType::SequenceOf5 => 'SequenceOf5',
            AnnounceType::SequenceOf6 => 'SequenceOf6',
            AnnounceType::SequenceOf7 => 'SequenceOf7',
            AnnounceType::SequenceOf8 => 'SequenceOf8',
            AnnounceType::FourOfAKind => 'FourOfAKind',
            AnnounceType::FourNines => 'FourNines',
            AnnounceType::FourJacks => 'FourJacks',
        };
    }
}
