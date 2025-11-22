<?php namespace App\Component\Dto\Actions;

use Symfony\Component\Serializer\Attribute\Context;
use App\Component\Serializer\Normalizer\ChessMoveDtoDenormalizer;

use App\Component\Dto\ChessMoveDto;
use App\Component\Dto\GameDto;
use App\Component\Type\PlayerColor;

class ChessOpponentMoveActionDto extends ActionDto
{
    public function __construct()
    {
        $this->actionName = ActionNames::chessOpponentMove->value;
    }
    
    /** @var ChessMoveDto $move */
    #[Context([ChessMoveDtoDenormalizer::class])]
    public ChessMoveDto $move;
    public PlayerColor $myColor;
    
    public ?GameDto $game;
}
