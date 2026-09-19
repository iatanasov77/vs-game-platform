<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Vankosoft\ApplicationBundle\Component\Status;

class TestMercureController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct( ManagerRegistry $doctrine, HubInterface $hub )
    {
        $this->doctrine = $doctrine;
        $this->hub      = $hub;
    }
    
    public function index(): Response
    {
        return $this->render( 'Pages/MercureTest/index.html.twig', [
            'controller_name' => 'IndexController',
        ]);
    }
    
    public function publish( HubInterface $hub ): JsonResponse
    {
        $update = new Update(
            '/test',
            \json_encode( ['update' => 'New update received at ' . date( "h:i:sa" )] )
        );
        
        $this->hub->publish( $update );
        $responseData   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Update published',
        ];
        
        return new JsonResponse( $responseData );
    }
}
