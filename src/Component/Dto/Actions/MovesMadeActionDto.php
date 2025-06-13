<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use App\Serializer\Normalizer\MovesMadeActionDtoDenormalizer;
use App\Component\Dto\MoveDto;

#[Context([MovesMadeActionDtoDenormalizer::class])]
class MovesMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::movesMade->value;
    }
    
    /** @var MoveDto[] $moves */
    public array $moves;
}
