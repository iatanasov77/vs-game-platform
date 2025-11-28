<?php namespace App\Component\Type;

enum ContractBridgeCardType: int
{
    case Two    = 0;
    case Three  = 1;
    case Four   = 2;
    case Five   = 3;
    case Six    = 4;
    case Seven  = 5;
    case Eight  = 6;
    case Nine   = 7;
    case Ten    = 8;
    case Jack   = 9;
    case Queen  = 10;
    case King   = 11;
    case Ace    = 12;
    
    public function toString(): string
    {
        return match( $this ) {
            ContractBridgeCardType::Two => '2',
            ContractBridgeCardType::Three => '3',
            ContractBridgeCardType::Four => '4',
            ContractBridgeCardType::Five => '5',
            ContractBridgeCardType::Six => '6',
            ContractBridgeCardType::Seven => '7',
            ContractBridgeCardType::Eight => '8',
            ContractBridgeCardType::Nine => '9',
            ContractBridgeCardType::Ten => '10',
            ContractBridgeCardType::Jack => 'J',
            ContractBridgeCardType::Queen => 'Q',
            ContractBridgeCardType::King => 'K',
            ContractBridgeCardType::Ace => 'A',
        };
    }
}
