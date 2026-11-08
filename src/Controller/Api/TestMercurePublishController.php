<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Vankosoft\ApplicationBundle\Component\Status;

use App\Component\Manager\MercureManager;
use App\Component\Dto\TestMercureMessageDto;

class TestMercurePublishController
{
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var MercureManager */
    private $manager;
    
    public function __construct( SerializerInterface $serializer, MercureManager $manager )
    {
        $this->serializer   = $serializer;
        $this->manager      = $manager;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $postData   = $this->serializer->deserialize( $request->getContent(), TestMercureMessageDto::class, JsonEncoder::FORMAT );
        $response   = $this->manager->DoAction( $postData->topic, $postData->message );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => $response,
        ];
        return new JsonResponse( $response );
    }
}
