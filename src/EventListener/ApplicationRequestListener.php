<?php namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use App\Entity\MercureConnection;

final class ApplicationRequestListener
{
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var RepositoryInterface */
    private $mercureConnectionsRepository;
    
    /** @var HubInterface */
    private $hub;
    
    /** @var string */
    private $projectDir;
    
    public function __construct(
        SecurityBridge $vsSecurityBridge,
        RepositoryInterface $mercureConnectionsRepository,
        HubInterface $hub,
        string $projectDir
    ) {
        $this->vsSecurityBridge             = $vsSecurityBridge;
        $this->mercureConnectionsRepository = $mercureConnectionsRepository;
        $this->hub                          = $hub;
        $this->projectDir                   = $projectDir;
    }
    
    public function onKernelRequest( RequestEvent $event )
    {
        $user       = $this->vsSecurityBridge->getUser();
        
        //\file_put_contents( $this->projectDir . '/var/debug_mercure.txt', $this->hub->getPublicUrl() );
        $connection = $user ? $this->mercureConnectionsRepository->findOneBy( ['user' => $user ] ) : null;
        //var_dump( $connection ); die;
        
       if ( $connection ) {
            $this->publishConnection( $connection );
        }
    }
    
    private function publishConnection( MercureConnection $connection ): void
    {
        $publishData    = json_encode([
            'type'      => 'activeConnectionUpdate',
            'action'    => 'login',
            'target'    => $connection->getUser()->getUsername(),
        ]);
        
        $update = new Update(
            '/active_connections',
            $publishData,
            false,
            null,
            'activeConnectionUpdate'
        );
        $this->hub->publish( $update );
    }
}