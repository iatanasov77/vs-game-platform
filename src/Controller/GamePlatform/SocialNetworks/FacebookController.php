<?php namespace App\Controller\GamePlatform\SocialNetworks;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Security\UserManager;
use Vankosoft\UsersBundle\Security\LoginFormAuthenticator;

use KnpU\OAuth2ClientBundle\Client\ClientRegistry;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

use App\Security\AppCustomAuthenticator;

class FacebookController extends AbstractController
{
    /** @var ManagerRegistry **/
    private $doctrine;
    
    /** @var RepositoryInterface **/
    private $usersRepository;
    
    /** @var UserManager **/
    private $usersManager;
    
    /** @var LoginFormAuthenticator **/
    private $authenticator;
    
    /** @var UserAuthenticatorInterface **/
    private $userAuthenticator;
    
    /** @var ClientRegistry **/
    private $clientRegistry;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $usersRepository,
        UserManager $usersManager,
        LoginFormAuthenticator $authenticator,
        UserAuthenticatorInterface $userAuthenticator,
        ClientRegistry $clientRegistry
    ) {
        $this->doctrine             = $doctrine;
        $this->usersRepository      = $usersRepository;
        $this->usersManager         = $usersManager;
        $this->authenticator        = $authenticator;
        $this->userAuthenticator    = $userAuthenticator;
        $this->clientRegistry       = $clientRegistry;
    }
    
    public function connectAction( Request $request ): Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'dashboard' );
        }
        return $this->clientRegistry->getClient( 'facebook_main' )->redirect([],[
            'public_profile', 'email'
        ]);
    }
    
    public function connectCheckAction( Request $request ): Response
    {
        if ( $this->getUser() ) {
            return $this->redirectToRoute( 'dashboard' );
        }
        
        $client = $this->clientRegistry->getClient( 'facebook_main' );
        
        try {
            $facebookUser = $client->fetchUser();
            
            // check if email exist
            $existingUser  = $this->usersRepository->findOneBy( ['email' => $facebookUser->getEmail()] );
            if( $existingUser ){
                return $this->userAuthenticator->authenticateUser(
                    $existingUser,
                    $this->authenticator,
                    $request
                );
            }
            
            $user = $this->usersManager->createUser( $facebookUser->getEmail(), $facebookUser->getEmail(), $facebookUser->getId() );
            $this->usersManager->saveUser( $user );
            
            return $this->userAuthenticator->authenticateUser(
                $user,
                $this->authenticator,
                $request
            );
        } catch ( IdentityProviderException $e ) {
            var_dump( $e->getMessage() ); die;
        }
    }
}