<?php namespace App\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\BidDto;

/**
 * REFERENCES
 * ==========
 * https://symfony.com/doc/current/serializer.html
 * https://stackoverflow.com/questions/70467989/how-to-deserialize-a-nested-array-of-objects-declared-on-the-constructor-via-pro
 */
class BidMadeActionDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] ): mixed
    {
        $bid      = $this->denormalizer->denormalize( $data['bid'], BidDto::class );
        
        $dto        = new BidMadeActionDto();
        $dto->bid = $bid;
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null, array $context = [] ): bool
    {
        return $type === BidMadeActionDto::class;
    }
    
    public function getSupportedTypes( ?string $format ): array
    {
        return [
            'object' => null,                   // doesn't support any classes or interfaces
            '*' => false,                       // supports any other types, but the decision is not cacheable
            BidMadeActionDto::class => true,  // supports MyCustomClass and decision is cacheable
        ];
    }
}
