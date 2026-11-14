<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\ServerSentEvent\MercureTokenProvider;

/**
 * Manual: https://github.com/symfony/mercure/issues/102
 * 
 * Debug JWT Tokens: https://www.jwt.io/
 */
class MercureGetSubscriptionsController extends AbstractController
{
    /** @var MercureTokenProvider */
    private $jwtFactory;
    
    /** @var HttpClientInterface */
    private $httpClient;
    
    public function __construct( MercureTokenProvider $jwtFactory, HttpClientInterface $httpClient, )
    {
        $this->jwtFactory   = $jwtFactory;
        $this->httpClient   = $httpClient;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $jwt            = $this->jwtFactory->getJwt();
        
        $subscriptions  = $this->httpClient->request( 'GET', $this->jwtFactory->getPublicUrl() . '/subscriptions', [
            'headers' => [
                'Authorization' => 'Bearer ' . $jwt,
                'Content-Type' => 'application/ld+json',
            ]
        ])->getContent();
        
        $response = [
            'status' => Status::STATUS_OK,
            'data' => [
                'subscriptions' => $subscriptions,
            ],
        ];
        
        return new JsonResponse( $response );
    }
}
