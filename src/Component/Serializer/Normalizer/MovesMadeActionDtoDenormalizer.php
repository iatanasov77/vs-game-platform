<?php namespace App\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\MoveDto;

/**
 * REFERENCES
 * ==========
 * https://symfony.com/doc/current/serializer.html
 * https://stackoverflow.com/questions/70467989/how-to-deserialize-a-nested-array-of-objects-declared-on-the-constructor-via-pro
 */
class MovesMadeActionDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] )
    {
        $moves      = \array_map(
            fn( $move ) => $this->denormalizer->denormalize( $move, MoveDto::class ),
            $data['moves']
        );
        
        $dto        = new MovesMadeActionDto();
        $dto->moves = $moves;
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null )
    {
        return $type === MovesMadeActionDto::class;
    }
    
    public function getSupportedTypes( ?string $format ): array
    {
        return [
            'object' => null,                   // doesn't support any classes or interfaces
            '*' => false,                       // supports any other types, but the decision is not cacheable
            MovesMadeActionDto::class => true,  // supports MyCustomClass and decision is cacheable
        ];
    }
}
