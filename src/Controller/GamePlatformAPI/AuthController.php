<?php namespace App\Controller\GamePlatformAPI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;

use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;

class AuthController extends AbstractController
{
    use GlobalFormsTrait;

    /** @var ApplicationContextInterface */
    private $applicationContext;
    
    /** @var Environment */
    private $templatingEngine;

    /** @var ManagerRegistry **/
    private $doctrine;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        ManagerRegistry $doctrine
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->doctrine             = $doctrine;
    }
    
    public function login( AuthenticationUtils $authenticationUtils, Request $request ): Response
    {
        if ( 
            $this->getUser() && 
            (
                $this->getUser()->getApplications()->contains( $this->applicationContext->getApplication() ) ||
                $this->isGranted( 'ROLE_APPLICATION_ADMIN', $this->getUser() )
            )
        ) {
            return $this->redirectToRoute( 'app_home' );
        }
        
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();
        //var_dump($error); die;
        
        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();
        
        $tplVars = [
            'shoppingCart'  => $this->getShoppingCart( $request ),
            'last_username' => $lastUsername,
            'error'         => $error,
        ];
        
        return new Response( $this->templatingEngine->render( $this->getTemplate(), $tplVars ) );
    }
    
    public function logout()
    {
        // controller can be blank: it will never be executed!
        throw new \Exception( 'Don\'t forget to activate logout in security.yaml' );
    }
    
    protected function getTemplate(): string
    {
        $template   = 'game-platform-api/Pages/login.html.twig';
        
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( ! $appSettings->isEmpty() && $appSettings[0]->getTheme() ) {
            $template   = 'Pages/login.html.twig';
        }
        
        return $template;
    }
}

