<?php namespace App\Controller\GamePlatformAPI;

use Vankosoft\CatalogBundle\Controller\PricingPlanCheckoutController as BasePricingPlanCheckoutController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PricingPlanCheckoutController extends BasePricingPlanCheckoutController
{
	use GlobalFormsTrait;
	
    public function showPricingPlans( Request $request ): Response
    {
        $pricingPlanCategories  = $this->pricingPlanCategoryRepository->findAll();
        
        return $this->render( '@VSCatalog/Pages/PricingPlansCheckout/pricing_plans.html.twig', [
            'pricingPlanCategories' => $pricingPlanCategories,
			'subscriptions'         => $this->subscriptionsRepository->getSubscriptionsByUser( $this->securityBridge->getUser() ),
			'shoppingCart'          => $this->getShoppingCart( $request ),
        ]);
    }
}