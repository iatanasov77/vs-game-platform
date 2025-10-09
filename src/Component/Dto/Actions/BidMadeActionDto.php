<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use App\Component\Serializer\Normalizer\BidMadeActionDtoDenormalizer;
use App\Component\Serializer\Normalizer\BidDtoDenormalizer;
use App\Component\Dto\BidDto;

#[Context([BidMadeActionDtoDenormalizer::class])]
class BidMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::bidMade->value;
    }
    
    /** @var BidDto $bid */
    #[Context([BidDtoDenormalizer::class])]
    public BidDto $bid;
}
