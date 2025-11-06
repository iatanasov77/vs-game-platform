<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;

class DebugController extends AbstractController
{
    protected $serviceContainer;
    
    public function __construct( ContainerInterface $container )
    {
        $this->serviceContainer = $container;
    }
    
    public function debugSession( Request $request ): Response
    {
        $session    = $this->serviceContainer->get( 'session' );
        $session->start();
        $session->set( 'debug_session', 'ECHO' );
        
        var_dump( $session->set( 'debug_session' ) ); die;
    }
        
    public function debugParameters( Request $request ): Response
    {
        var_dump( $this->getParameter( 'vs_users.coockie.domain' ) ); die;
    }
}
