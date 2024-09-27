<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\Websocket\WebsocketClientFactory;

class ZmqPublishController extends AbstractController
{
    /** @var WebsocketClientFactory */
    private $wsClientFactory;
    
    public function __construct( WebsocketClientFactory $wsClientFactory )
    {
        $this->wsClientFactory  = $wsClientFactory;
    }
    
    public function publish( Request $request ): JsonResponse
    {
        $data       = \json_decode( $request->getContent() );
        
        try {
            $this->wsClientFactory->createPublisherClient()->send( $data );
        } catch ( \Exception $e ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => $e->getMessage(),
            ]);
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
        ]);
    }
}
