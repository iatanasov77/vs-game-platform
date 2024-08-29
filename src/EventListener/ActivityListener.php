<?php namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;
use Doctrine\Persistence\ManagerRegistry;
use Vankosoft\UsersBundle\Security\SecurityBridge;

final class ActivityListener
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var SecurityBridge */
    private $securityBridge;
    
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityBridge $securityBridge
    ) {
        $this->doctrine         = $doctrine;
        $this->securityBridge   = $securityBridge;
    }
    
    public function onKernelRequest( RequestEvent $event )
    {
        $user   = $this->securityBridge->getUser();
        
        if ( $user ) {
            $user->setLastActiveAt( new \DateTime() );
            
            $em = $this->doctrine->getManager();
            $em->persist( $user );
            $em->flush();
        }
    }
}