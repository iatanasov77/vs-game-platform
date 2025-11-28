<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\Collection;
use App\Component\Type\BidType;
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerGetAnnouncesContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Rules\CardGame\BridgeBeloteGameMechanics\RoundResult;

interface PlayerInterface
{
    public function GetBid( PlayerGetBidContext $context ): BidType;
    
    public function GetAnnounces( PlayerGetAnnouncesContext $context ): Collection;
    
    public function PlayCard( PlayerPlayCardContext $context ): PlayCardActionDto;
    
    public function EndOfTrick( Collection $trickActions ): void;
    
    public function EndOfRound( RoundResult $roundResult ): void;
    
    public function EndOfGame( GameResult $gameResult ): void;
}
