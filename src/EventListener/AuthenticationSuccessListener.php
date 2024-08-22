<?php namespace App\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\AuthenticationEvents;
use Symfony\Component\Security\Core\Event\AuthenticationSuccessEvent;
use Doctrine\Persistence\ManagerRegistry;
use SymfonyCasts\Bundle\ResetPassword\Generator\ResetPasswordRandomGenerator;
use Vankosoft\ApiBundle\Model\Interfaces\ApiUserInterface;

class AuthenticationSuccessListener implements EventSubscriberInterface
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var ResetPasswordRandomGenerator */
    private $randomGenerator;
    
    public function __construct( ManagerRegistry $doctrine, ResetPasswordRandomGenerator $generator )
    {
        $this->doctrine         = $doctrine;
        $this->randomGenerator  = $generator;
    }
    
    public function onAuthenticationSuccess( AuthenticationSuccessEvent $event ): void
    {
        $user       = $event->getAuthenticationToken()->getUser();
        if ( ! ( $user instanceof ApiUserInterface ) ) {
            return;
        }
        
        $verifier   = $this->randomGenerator->getRandomAlphaNumStr();
        $expiresAt  = new \DateTimeImmutable( \sprintf( '+%d seconds', 3600 ) );
        
        $user->setApiVerifySiganature( $verifier );
        $user->setApiVerifyExpiresAt( \DateTime::createFromImmutable( $expiresAt ) );
        
        $em         = $this->doctrine->getManager();
        $em->persist( $user );
        $em->flush();
    }
    
    public static function getSubscribedEvents(): array
    {
        return [
            AuthenticationEvents::AUTHENTICATION_SUCCESS => ['onAuthenticationSuccess', 9999],
        ];
    }
}