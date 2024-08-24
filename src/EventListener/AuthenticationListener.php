<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;

final class AuthenticationListener implements EventSubscriberInterface
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $mercureConnectionsRepository;
    
    /** @var FactoryInterface */
    private $mercureConnectionsFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $mercureConnectionsRepository,
        FactoryInterface $mercureConnectionsFactory
    ) {
        $this->doctrine                     = $doctrine;
        $this->mercureConnectionsRepository = $mercureConnectionsRepository;
        $this->mercureConnectionsFactory    = $mercureConnectionsFactory;
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
    }
    
    public function onLogout( LogoutEvent $event ): void
    {
        $token  = $event->getToken();
        if ( $token ) {
            $user   = $token->getUser();
            $connection = $this->mercureConnectionsRepository->findOneBy( ['user' => $user] );
            if ( $connection ) {
                $connection->setActive( false );
                
                $em         = $this->doctrine->getManager();
                $em->persist( $connection );
                $em->flush();
            }
        }
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => 'onAuthenticationSuccess',
            LogoutEvent::class => 'onLogout',
        ];
    }
    
}