<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use App\Component\Serializer\Normalizer\ChessMoveDtoDenormalizer;
use App\Component\Dto\ChessMoveDto;

class ChessMoveMadeActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::chessMoveMade->value;
    }
    
    /** @var ChessMoveDto $move */
    #[Context([ChessMoveDtoDenormalizer::class])]
    public ChessMoveDto $move;
}
