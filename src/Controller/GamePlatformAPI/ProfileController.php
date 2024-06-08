<?php namespace App\Controller\GamePlatformAPI;

use Vankosoft\UsersBundle\Controller\ProfileController as BaseProfileController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Currencies;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\CmsBundle\Component\Uploader\FileUploaderInterface;
use Vankosoft\UsersBundle\Security\UserManager;
use Vankosoft\PaymentBundle\Component\Payment\Payment;

class ProfileController extends BaseProfileController
{
	/** @var Payment */
    private $vsPayment;
	
    /** @var RepositoryInterface */
    private $pricingPlanRepository;
    
    /** @var RepositoryInterface */
    private $pricingPlanCategoryRepository;
    
    /** @var RepositoryInterface */
    private $pricingPlanSubscriptionRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        string $usersClass,
        UserManager $userManager,
        FactoryInterface $avatarImageFactory,
        FileUploaderInterface $imageUploader,
		Payment $vsPayment,
        RepositoryInterface $pricingPlanRepository,
        RepositoryInterface $pricingPlanCategoryRepository,
        RepositoryInterface $pricingPlanSubscriptionRepository
    ) {
        parent::__construct(
            $doctrine,
            $usersClass,
            $userManager,
            $avatarImageFactory,
            $imageUploader
        );
        
		$this->vsPayment                        	= $vsPayment;
        $this->pricingPlanRepository                = $pricingPlanRepository;
        $this->pricingPlanCategoryRepository        = $pricingPlanCategoryRepository;
        $this->pricingPlanSubscriptionRepository    = $pricingPlanSubscriptionRepository;
    }
    
    public function showAction( Request $request ): Response
    {
        if ( ! $this->getUser() ) {
            return $this->redirectToRoute( 'app_home' );
        }
        
        $profileEditForm    = $this->getProfileEditForm();
        $otherForms         = $this->getOtherForms();
        
		$activeSubscriptions    = $this->pricingPlanSubscriptionRepository
                                        ->getActiveSubscriptionsByUser( $this->getUser() );
        
        $subscriptionsRoutes    = [];
        foreach ( $activeSubscriptions as $subscription ) {
            $subscriptionsRoutes[$subscription->getId()]    = [
                'createRecurring'   => $this->vsPayment->getPaymentCreateRecurringRoute( $subscription->getGateway() ),
                'cancelRecurring'   => $this->vsPayment->getPaymentCancelRecurringRoute( $subscription->getGateway() ),
            ];
        }
		
        $params = [
            'profileEditForm'       => $profileEditForm->createView(),
            'changePasswordForm'    => $otherForms['changePasswordForm']->createView(),
            
            'subscriptions'         => $this->pricingPlanSubscriptionRepository->getSubscriptionsByUser( $this->getUser() ),
			'subscriptionsRoutes'   => $subscriptionsRoutes,
        ];
        
        return $this->render( '@VSUsers/Profile/show.html.twig',
            array_merge( $params, $this->templateParams( $profileEditForm ) )
        );
    }
}