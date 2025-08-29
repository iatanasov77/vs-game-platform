<?php namespace App\Component\Type;

enum AnnounceType: int
{
    case Belot = 1;
    case SequenceOf3 = 2; // 20
    case SequenceOf4 = 3; // 50
    case SequenceOf5 = 4; // 100
    case SequenceOf6 = 5; // 100
    case SequenceOf7 = 6; // 100
    case SequenceOf8 = 7; // 100
    case FourOfAKind = 8; // 100
    case FourNines = 9; // 150
    case FourJacks = 10; // 200
    
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
