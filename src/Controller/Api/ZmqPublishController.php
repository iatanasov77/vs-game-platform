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
        $wampAction = \json_decode( $request->getContent() );
        
        try {
            $this->wsClientFactory->createZmqClient()->send( $wampAction );
        } catch ( \Exception $e ) {
            return new JsonResponse([
                'status'    => Status::STATUS_ERROR,
                'message'   => $e->getMessage(),
            ]);
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'topic'     => $wampAction->topic,
        ]);
    }
}
