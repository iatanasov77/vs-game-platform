<?php namespace App\Component\ServerSentEvent;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Mercure\Authorization;
use Symfony\Component\Mercure\Update;
use App\Component\MercureLogger;

/*
 * Manual: https://symfony.com/doc/current/mercure.html#programmatically-setting-the-cookie
 * 
 * You cannot use the mercure() twig helper and the setCookie() method at the same time (it would set the cookie twice on a single request).
 * Choose either one method or the other.
 */
final class MercurePublisher
{
    /** @var MercureLogger */
    private $logger;
    
    /** @var HubInterface */
    private $hub;
    
    /** @var Discovery */
    private $discovery;
    
    /** @var Authorization */
    private $authorization;
    
    public function __construct(
        MercureLogger $logger,
        HubInterface $hub,
        Discovery $discovery,
        Authorization $authorization
    ) {
        $this->logger           = $logger;
        $this->hub              = $hub;
        $this->discovery        = $discovery;
        $this->authorization    = $authorization;
    }
    
    public function publish( Request $request, Update $update )
    {
        try {
            $this->discovery->addLink( $request );
            $this->authorization->setCookie( $request, $update->getTopics() );
            
            $this->hub->publish( $update );
        } catch ( MercureRuntimeException $e ) {
            $this->logger->log( "MercureRuntimeException: {$e->getMessage()}" );
            
            throw $e;
        }
    }
}