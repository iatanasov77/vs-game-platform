<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Firebase\JWT\JWT;
use Vankosoft\ApplicationBundle\Component\Status;

use App\Component\ServerSentEvent\JwtToken;

/**
 * Manual: https://stackoverflow.com/a/62532899/12693473
 */
class GenerateMercureTokenController extends AbstractController
{
    /** @var TokenFactoryInterface */
    private $jwtFactory;
    
    public function __construct( TokenFactoryInterface $jwtFactory )
    {
        $this->jwtFactory = $jwtFactory;
    }
    
    public function index( $factory, Request $request ): JsonResponse
    {
        switch ( $factory ) {
            case JwtToken::FactorySymfonyMercure:
                $jwt = $this->generateWithSymfonyMercure();
                break;
            case JwtToken::FactoryFirebaseJwt:
                $jwt = $this->generateWithFirebaseJwt();
                break;
            default:
                throw new \RuntimeException( 'Undefined WebToken Factory !!!' );
        }
        
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
            [],
            [
                'aud'       => 'http://mercure-hub.game-platform.lh/.well-known/mercure',
                'iss'       => 'https://localhost',
                'client_id' => 'https://localhost',
                'sub'       => 'my-service-subject',
            ]
        );
    }
    
    private function generateWithFirebaseJwt(): string
    {
        $key = $this->getParameter( 'vsapp.mercure_jwt_secret' );
        $payload = [
            'mercure' => [
                'publish' => ['*'],
            ],
        ];
        $jwt = JWT::encode( $payload, $key, 'HS256' ); // holds valid jwt now
        
        return $jwt;
    }
}
