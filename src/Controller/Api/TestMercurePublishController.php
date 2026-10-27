<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Vankosoft\ApplicationBundle\Component\Status;

class TestMercurePublishController
{
    /** @var HubInterface */
    private $hub;
    
    public function __construct( HubInterface $hub )
    {
        $this->hub = $hub;
    }
    
    public function sendTestMessageAction( Request $request ): JsonResponse
    {
        $postData = \json_decode( $request->getContent(), true );
        $publishData    = json_encode([
            'status'   => 'checked out',
        ]);
        
        $update = new Update(
            $postData['topic'],
            $publishData,
        );
        
        $this->hub->publish( $update );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Published From Backend',
        ];
        
        return new JsonResponse( $response );
    }
}
