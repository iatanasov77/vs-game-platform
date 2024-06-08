<?php namespace App\Controller\GamePlatformAPI;

use Vankosoft\ApplicationBundle\Controller\ContactController as BaseContactController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;

use Vankosoft\UsersBundle\Component\UserNotifications;
use Vankosoft\ApplicationBundle\Form\ContactForm;

use Doctrine\Persistence\ManagerRegistry;

class ContactController extends BaseContactController
{
    use GlobalFormsTrait;
    
    /** @var ManagerRegistry **/
    private $doctrine;
    
    public function __construct( array $params, MailerInterface $mailer, UserNotifications $notifications, ManagerRegistry $doctrine )
    {
        parent::__construct( $params, $mailer, $notifications );
        
        $this->doctrine = $doctrine;
    }
    
    public function index( Request $request ): Response
    {
        $form   = $this->createForm( ContactForm::class, null, ['method' => 'POST'] );
        
        $form->handleRequest( $request );
        if( $form->isSubmitted() && $form->isValid() ) {
            $this->sendEmail( $form->getData(), $this->params['contactEmail'] );
            
            return $this->redirect( $this->generateUrl( 'vs_application_contact' ) );
        }
        
        return $this->render( '@VSApplication/Pages/contact.html.twig', [
            'form'              => $form->createView(),
            'contactEmail'      => $this->params['contactEmail'],
            'showAddress'       => $this->params['showAddress'],
            'showPhone'         => $this->params['showPhone'],
            'googleMap'         => $this->params['googleMap'],
            'googleLargeMap'    => $this->params['googleLargeMap'],
            
            'shoppingCart'      => $this->getShoppingCart( $request ),
        ]);
    }
}
