<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use App\Component\Serializer\Normalizer\MoveDtoDenormalizer;
use App\Component\Dto\MoveDto;

class OpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentMove->value;
    }
    
    /** @var MoveDto $move */
    #[Context([MoveDtoDenormalizer::class])]
    public MoveDto $move;
}
