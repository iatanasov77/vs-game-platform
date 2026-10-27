<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Mercure\Jwt\WebTokenFactory;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Jwt\Grant;
use Vankosoft\ApplicationBundle\Component\Status;

/**
 * Manual: https://stackoverflow.com/a/62532899/12693473
 * 
 * Debug JWT Tokens: https://www.jwt.io/
 */
class GenerateMercureTokenController extends AbstractController
{
    /** @var TokenFactoryInterface */
    private $jwtFactory;
    
    public function __construct( TokenFactoryInterface $jwtFactory )
    {
        $this->jwtFactory = $jwtFactory;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $jwt = $this->generateWithSymfonyMercure();
        
        $response = [
            'status' => Status::STATUS_OK,
            'data' => [
                'token' => $jwt,
            ],
        ];
        
        return new JsonResponse( $response );
    }
    
    private function generateWithSymfonyMercure(): string
    {
        return $this->jwtFactory->create(
            [
                new Grant( [Grant::ACTION_PUBLISH], ['*'] ),
                new Grant( [Grant::ACTION_SUBSCRIBE], ['*'] ),
            ],
            [
                'aud'       => 'http://mercure-hub.game-platform.lh/.well-known/mercure',
                'iss'       => 'https://localhost',
                'client_id' => 'https://localhost',
                'sub'       => 'my-service-subject',
            ]
        );
    }
}
