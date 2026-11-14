<?php namespace App\Component\ServerSentEvent;

use Symfony\Component\Mercure\Jwt\TokenProviderInterface;
use Symfony\Component\Mercure\Jwt\TokenFactoryInterface;
use Symfony\Component\Mercure\Jwt\Grant;

final class MercureTokenProvider implements TokenProviderInterface
{
    /** @var TokenFactoryInterface */
    private $jwtFactory;
    
    /** @var string */
    private $publicUrl;
    
    public function __construct( TokenFactoryInterface $jwtFactory, string $publicUrl )
    {
        $this->jwtFactory   = $jwtFactory;
        $this->publicUrl    = $publicUrl;
    }
    
    public function getJwt(): string
    {
        return $this->jwtFactory->create(
            [
                new Grant( [Grant::ACTION_PUBLISH], ['*'] ),
                new Grant( [Grant::ACTION_SUBSCRIBE], ['*'] ),
                new Grant( ['payload'], [] ),
            ],
            [
                // 'aud'       => "http://mercure-hub.game-platform.lh/.well-known/mercure",
                'aud'       => $this->publicUrl,
                'iss'       => 'https://localhost',
                'client_id' => 'https://localhost',
                'sub'       => 'my-service-subject',
            ]
        );
    }
    
    public function getPublicUrl(): string
    {
        return $this->publicUrl;
    }
}
