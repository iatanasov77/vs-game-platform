<?php namespace App\Controller\GamePlatformAPI;

use Vankosoft\PaymentBundle\Controller\Checkout\CreditCardController as BaseCreditCardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

use Vankosoft\PaymentBundle\Exception\ShoppingCartException;

class CreditCardController extends BaseCreditCardController
{
    use GlobalFormsTrait;
    
    public function showCreditCardFormAction( $formAction, Request $request ): Response
    {
        $cartId = $request->getSession()->get( 'vs_payment_basket_id' );
        if ( ! $cartId ) {
            throw new ShoppingCartException( 'Shopping Cart not exist in session !!!' );
        }
        $cart   = $this->ordersRepository->find( $cartId );
        if ( ! $cart ) {
            throw new ShoppingCartException( 'Shopping Cart not exist in repository !!!' );
        }
        
        $paymentMethod  = $cart->getPaymentMethod();
        $gatewayConfig  = (
                            $paymentMethod->getGateway()->getFactoryName() == 'stripe_checkout' ||
                            $paymentMethod->getGateway()->getFactoryName() == 'stripe_js'
                        ) ? $paymentMethod->getGateway()->getConfig() : '';
						
		$form           = $this->getCreditCardForm( base64_decode( $formAction ) );
        
        if( $request->isXmlHttpRequest() ) {
            return $this->render( '@VSPayment/Pages/CreditCard/Partial/CreditCardForm.html.twig', [
                'form'          => $form->createView(),
                'paymentMethod' => $paymentMethod,
                'captureKey'    => $gatewayConfig['publishable_key'],
                'formAction'    => '',
                'formMethod'    => 'POST',
            ]);
        } else {
            return $this->render( '@VSPayment/Pages/CreditCard/credit_card.html.twig', [
                'form'          => $form->createView(),
                'paymentMethod' => $paymentMethod,
                'captureKey'    => $gatewayConfig['publishable_key'],
                'shoppingCart'  => $this->getShoppingCart( $request ),
            ]);
        }
    }
}