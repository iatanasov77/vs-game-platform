<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use App\Component\Dto\MoveDto;

class OpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::opponentMove->value;
    }
    
    #[Context([ObjectNormalizer])]
    public MoveDto $move;
}
