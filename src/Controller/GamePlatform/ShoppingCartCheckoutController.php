<?php namespace App\Controller\GamePlatform;

use Vankosoft\PaymentBundle\Controller\ShoppingCart\ShoppingCartCheckoutController as BaseShoppingCartCheckoutController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Vankosoft\PaymentBundle\Form\PaymentForm;

class ShoppingCartCheckoutController extends BaseShoppingCartCheckoutController
{
    use GlobalFormsTrait;
    
    public function showPaymentMethodsFormAction( Request $request ): Response
    {
        $paymentDescription = $request->query->get( 'payment_description' );
        $form               = $this->createForm( PaymentForm::class );
        
        return $this->render( '@VSPayment/Pages/Payment/payment-form.html.twig', [
            'form'                  => $form->createView(),
            'paymentDescription'    => $paymentDescription ?: 'VankoSoft Payment',
            'shoppingCart'          => $this->getShoppingCart( $request ),
        ]);
    }
}