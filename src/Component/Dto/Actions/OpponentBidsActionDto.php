<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use Doctrine\Common\Collections\Collection;
use App\Component\Serializer\Normalizer\BidDtoDenormalizer;
use App\Component\Dto\BidDto;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

class OpponentBidsActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentBids->value;
    }
    
    /** @var BidDto $bid */
    #[Context([BidDtoDenormalizer::class])]
    public BidDto $bid;
    
    public array $validBids;
    public PlayerPosition $nextPlayer;
    public GameState $playState;
    
    // Debug Player Cards
    public Collection $MyCards;
}
