<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use Vankosoft\ApiBundle\Security\ApiManager;
use Vankosoft\ApplicationBundle\Component\Status;

class DefaultController extends AbstractController
{
    /** @var SecurityBridge */
    protected $vsSecurityBridge;
    
    /** @var ApiManager */
    protected $apiManager;
    
    public function __construct(
        SecurityBridge $vsSecurityBridge,
        ApiManager $apiManager
    ) {
        $this->vsSecurityBridge = $vsSecurityBridge;
        $this->apiManager       = $apiManager;
    }
    
    public function getVerifySignatureAction( Request $request ): Response
    {
        $user                   = $this->vsSecurityBridge->getUser();
        $signatureComponents    = null;
        if ( $user ) {
            $signatureComponents    = $this->apiManager->getVerifySignature( $user, 'vs_api_login_by_signature' );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'signedUrl' => $signatureComponents ? $signatureComponents->getSignedUrl() : null,
        ]);
    }
}
