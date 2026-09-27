<?php namespace App\Component\ServerSentEvent;

use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;

final class MyTokenProvider implements TokenProviderInterface
{
    /** @var JWTTokenManagerInterface */
    private $jwtManager;
    
    /** @var SecurityBridge */
    private $securityBridge;
    
    public function __construct( JWTTokenManagerInterface $jwtManager, SecurityBridge $securityBridge )
    {
        $this->jwtManager       = $jwtManager;
        $this->securityBridge   = $securityBridge;
    }
    
    public function getJwt(): string
    {
        $user   = $this->securityBridge->getUser();
        $token  = $this->jwtManager->create( $user );
        
        return $token;
    }
}
