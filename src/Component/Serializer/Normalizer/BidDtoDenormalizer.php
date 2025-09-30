<?php namespace App\Component\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Doctrine\Common\Collections\ArrayCollection;
use App\Component\Dto\BidDto;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

/**
 * REFERENCES
 * ==========
 * https://symfony.com/doc/current/serializer.html
 * https://stackoverflow.com/questions/70467989/how-to-deserialize-a-nested-array-of-objects-declared-on-the-constructor-via-pro
 */
class BidDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] ): mixed
    {
        $dto            = new BidDto();
        
        $dto->Player    = PlayerPosition::from( $data['Player'] );
        $dto->Type      = BidType::fromValue( $data['Type'] );
        $dto->NextBids  = new ArrayCollection( $data['NextBids'] );
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null, array $context = [] ): bool
    {
        return $type === BidDto::class;
    }
    
    public function getSupportedTypes( ?string $format ): array
    {
        return [
            'object' => null,       // doesn't support any classes or interfaces
            '*' => false,           // supports any other types, but the decision is not cacheable
            BidDto::class => true, // supports MyCustomClass and decision is cacheable
        ];
    }
}
