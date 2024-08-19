<?php namespace App\Controller\GamePlatform;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Vankosoft\UsersSubscriptionsBundle\Component\PayedService\SubscriptionPeriod;

use App\Entity\Payment\Order;

trait GlobalFormsTrait
{
    protected function getShoppingCart( Request $request )
    {
        $session = $request->getSession();
        $session->start();  // Ensure Session is Started
        
        $cartId         = $session->get( 'vs_payment_basket_id' );
        $shoppingCart   = $cartId ? $this->_getDoctrine()->getRepository( Order::class )->find( $cartId ) : null;
        if ( ! $shoppingCart ) {
            $shoppingCart   = new Order();
            
            $shoppingCart->setUser( $this->_getAppUser() );
            $shoppingCart->setSessionId( $session->getId() );
            
            $this->_getDoctrine()->getManager()->persist( $shoppingCart );
            $this->_getDoctrine()->getManager()->flush();
        }
        
        return $shoppingCart;
    }
    
    protected function _getAppUser():? UserInterface
    {
        if ( $this->container->has( 'vs_users.security_bridge' ) ) {
            return $this->container->get( 'vs_users.security_bridge' )->getUser();
        }

        return $this->getUser();
    }
    
    protected function _getDoctrine()
    {
        if ( \method_exists( $this, 'getDoctrine' ) ) {
            return $this->getDoctrine();
        } else {
            return $this->doctrine;
        }
    }
}
