<?php namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpClient\Chunk\ServerSentEvent;
use Symfony\Component\HttpClient\EventSourceHttpClient;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;

final class ApplicationRequestListener
{
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var RepositoryInterface */
    private $mercureConnectionsRepository;
    
    /** @var EventSourceHttpClient */
    private $eventSourceClient;
    
    public function __construct(
        RequestStack $requestStack,
        SecurityBridge $vsSecurityBridge,
        RepositoryInterface $mercureConnectionsRepository
    ) {
        $this->vsSecurityBridge             = $vsSecurityBridge;
        $this->mercureConnectionsRepository = $mercureConnectionsRepository;
        
        $this->eventSourceClient            = new EventSourceHttpClient();
    }
    
    public function onKernelRequest( RequestEvent $event )
    {
        $user       = $this->vsSecurityBridge->getUser();
        
        $connection = $user ? $this->mercureConnectionsRepository->findOneBy( ['user' => $user ] ) : null;
        if ( $connection ) {
            //$this->publishConnection();
        }
    }
    
    private function publishConnection()
    {
        $source = $this->eventSourceClient->connect( 'https://localhost:8080/events' );
        while ( $source ) {
            foreach ( $this->eventSourceClient->stream( $source, 2 ) as $r => $chunk ) {
                if ( $chunk->isTimeout() ) {
                    // ...
                    continue;
                }
                
                if ( $chunk->isLast() ) {
                    // ...
                    
                    return;
                }
                
                // this is a special ServerSentEvent chunk holding the pushed message
                if ( $chunk instanceof ServerSentEvent ) {
                    // do something with the server event ...
                }
            }
        }
    }
}