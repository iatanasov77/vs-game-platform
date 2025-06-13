<?php namespace App\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use App\Component\Dto\Actions\MovesMadeActionDto;
use App\Component\Dto\MoveDto;

class MovesMadeActionDtoDenormalizer implements DenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    
    public function denormalize( mixed $data, string $type, ?string $format = null, array $context = [] )
    {
        $moves      = \array_map(
            fn( $move ) => $this->denormalizer->denormalize( $move, MoveDto::class ),
            $data['addressBook']
        );
        
        $dto        = new MovesMadeActionDto();
        $dto->moves = $moves;
        
        return $dto;
    }
    
    public function supportsDenormalization( mixed $data, string $type, ?string $format = null )
    {
        return $type === MovesMadeActionDto::class;
    }
}
