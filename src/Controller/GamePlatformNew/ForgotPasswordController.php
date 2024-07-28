<?php namespace App\Controller\GamePlatformNew;

use Vankosoft\UsersBundle\Controller\ForgotPasswordController as BaseForgotPasswordController;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use SymfonyCasts\Bundle\ResetPassword\Exception\ExpiredResetPasswordTokenException;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;

use Vankosoft\UsersBundle\Repository\ResetPasswordRequestRepository;
use Vankosoft\UsersBundle\Security\UserManager;

class ForgotPasswordController extends BaseForgotPasswordController
{
    use GlobalFormsTrait;
    
    public function __construct(
        ManagerRegistry $doctrine,
        ResetPasswordRequestRepository $repository,
        RepositoryInterface $usersRepository,
        MailerInterface $mailer,
        Factory $resetPasswordRequestFactory,
        UserManager $userManager,
        array $parameters
    ) {
        parent::__construct( $doctrine, $repository, $usersRepository, $mailer, $resetPasswordRequestFactory, $userManager, $parameters );
    }
    
    public function indexAction( Request $request, MailerInterface $mailer ) : Response
    {
        $form   = $this->getForgotPasswordForm();
        $form->handleRequest( $request );
        if (  $form->isSubmitted() ) {
            return parent::indexAction( $request, $mailer );
        }
        
        $params = [
            'form'          => $form->createView(),
            
            'shoppingCart'  => $this->getShoppingCart( $request ),
        ];
        return $this->render( '@VSUsers/Resetting/forgot_password.html.twig', $params );
    }
    
    public function resetAction( string $token, Request $request ) : Response
    {
        $tokenExpired   = false;
        try {
            $oUser   = $this->resetPasswordHelper->validateTokenAndFetchUser( $token );
        } catch ( ExpiredResetPasswordTokenException $e ) {
            $tokenExpired   = true;
        }
        
        $form   = $this->getChangePasswordForm( $token );
        $form->handleRequest( $request );
        if ( $form->isSubmitted() && ! $tokenExpired ) {
            return parent::resetAction( $token, $request );
        }
        
        $params = [
            'user'          => $oUser,
            'token'         => $token,
            'form'          => $form->createView(),
            
            'shoppingCart'  => $this->getShoppingCart( $request ),
        ];
        return $this->render( '@VSUsers/Resetting/change_password.html.twig', $params );
    }
}
