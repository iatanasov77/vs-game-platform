<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use App\Entity\MercureConnection;

final class AuthenticationListener implements EventSubscriberInterface
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $mercureConnectionsRepository;
    
    /** @var FactoryInterface */
    private $mercureConnectionsFactory;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $mercureConnectionsRepository,
        FactoryInterface $mercureConnectionsFactory,
        HubInterface $hub
    ) {
        $this->doctrine                     = $doctrine;
        $this->mercureConnectionsRepository = $mercureConnectionsRepository;
        $this->mercureConnectionsFactory    = $mercureConnectionsFactory;
        $this->hub                          = $hub;
    }
    
    public function onAuthenticationSuccess( AuthenticationSuccessEvent $event ): void
    {
        $user       = $event->getAuthenticationToken()->getUser();
        $connection = $this->mercureConnectionsRepository->findOneBy( ['user' => $user] );
        if ( ! $connection ) {
            $connection = $this->mercureConnectionsFactory->createNew();
            $connection->setUser( $user );
        }
        
        $connection->setActive( true );
        
        $em         = $this->doctrine->getManager();
        $em->persist( $connection );
        $em->flush();
        
        $this->publishConnection( $connection, 'login' );
    }
    
    public function onLogout( LogoutEvent $event ): void
    {
        $token  = $event->getToken();
        if ( $token ) {
            $user   = $token->getUser();
            $connection = $this->mercureConnectionsRepository->findOneBy( ['user' => $user] );
            
            if ( ! $connection ) {
                $connection = $this->mercureConnectionsFactory->createNew();
                $connection->setUser( $user );
            }
            
            $connection->setActive( false );
            
            $em         = $this->doctrine->getManager();
            $em->persist( $connection );
            $em->flush();
            
            $this->publishConnection( $connection, 'logout' );
        }
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
            LogoutEvent::class => 'onLogout',
        ];
    }
    
    private function publishConnection( MercureConnection $connection, string $action ): void
    {
        $publishData    = json_encode([
            'type'      => 'activeConnectionUpdate',
            'action'    => $action,
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