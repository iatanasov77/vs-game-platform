<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\ServerSentEvent\MercureTokenProvider;

/**
 * Manual: https://stackoverflow.com/a/62532899/12693473
 * 
 * Debug JWT Tokens: https://www.jwt.io/
 */
class MercureGenerateTokenController extends AbstractController
{
    /** @var MercureTokenProvider */
    private $jwtFactory;
    
    public function __construct( MercureTokenProvider $jwtFactory )
    {
        $this->jwtFactory = $jwtFactory;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $jwt = $this->jwtFactory->getJwt();
        
        $response = [
            'status' => Status::STATUS_OK,
            'data' => [
                'token' => $jwt,
            ],
        ];
        
        return new JsonResponse( $response );
    }
}
