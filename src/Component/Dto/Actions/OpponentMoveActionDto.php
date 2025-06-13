<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use App\Component\Dto\MoveDto;

class OpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentMove->value;
    }
    
    /** @var MoveDto $move */
    #[Context([ArrayDenormalizer::class])]
    public MoveDto $move;
}
