<?php namespace App\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use App\Component\Dto\MoveDto;
use App\Component\Type\PlayerColor;

/**
 * REFERENCES
 * ==========
 * https://symfony.com/doc/current/serializer.html
 * https://stackoverflow.com/questions/70467989/how-to-deserialize-a-nested-array-of-objects-declared-on-the-constructor-via-pro
 */
class MoveDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] )
    {
        $dto        = new MoveDto();
        
        $dto->from      = $data['from'];
        $dto->to        = $data['to'];
        $dto->animate   = $data['animate'];
        $dto->hint      = isset( $data['hint'] ) ? $data['hint'] : false;
        $dto->color     = PlayerColor::from( $data['color'] );
        $dto->nextMoves = new ArrayCollection( $data['nextMoves'] );
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null )
    {
        return $type === MoveDto::class;
    }
    
    public function getSupportedTypes( ?string $format ): array
    {
        return [
            'object' => null,       // doesn't support any classes or interfaces
            '*' => false,           // supports any other types, but the decision is not cacheable
            MoveDto::class => true, // supports MyCustomClass and decision is cacheable
        ];
    }
}
