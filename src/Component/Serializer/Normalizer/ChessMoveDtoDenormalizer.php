<?php namespace App\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use App\Component\Dto\ChessMoveDto;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessMoveType;
use App\Component\Type\ChessPieceType;

/**
 * REFERENCES
 * ==========
 * https://symfony.com/doc/current/serializer.html
 * https://stackoverflow.com/questions/70467989/how-to-deserialize-a-nested-array-of-objects-declared-on-the-constructor-via-pro
 */
class ChessMoveDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] ): mixed
    {
        $dto        = new ChessMoveDto();
        
        $dto->from      = $data['from'];
        $dto->to        = $data['to'];
        $dto->causeCheck = $data['causeCheck'];
        $dto->animate   = $data['animate'];
        $dto->hint      = isset( $data['hint'] ) ? $data['hint'] : false;
        $dto->color     = PlayerColor::from( $data['color'] );
        $dto->type      = ChessMoveType::from( $data['type'] );
        $dto->nextMoves = new ArrayCollection( $data['nextMoves'] );
        
        /*
        $dto->piece             = ChessPieceType::from( $data['type'] );
        $dto->capturedPiece     = ChessPieceType::from( $data['type'] );
        $dto->promoPiece        = ChessPieceType::from( $data['type'] );
        $dto->enpassantPiece    = ChessPieceType::from( $data['type'] );
        */
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null, array $context = [] ): bool
    {
        return $type === ChessMoveDto::class;
    }
    
    public function getSupportedTypes( ?string $format ): array
    {
        return [
            'object' => null,       // doesn't support any classes or interfaces
            '*' => false,           // supports any other types, but the decision is not cacheable
            ChessMoveDto::class => true, // supports MyCustomClass and decision is cacheable
        ];
    }
}
