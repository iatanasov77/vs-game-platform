<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\ApiBundle\Security\ApiManager;
use Vankosoft\ApplicationBundle\Component\Status;

class DefaultController extends AbstractController
{
    /** @var ApiManager */
    protected $apiManager;
    
    public function getVerifySignatureAction( Request $request ): Response
    {
        $signature  = null;
        if ( $this->getUser() ) {
            $signature  = $this->apiManager->getVerifySignature( $this->getUser(), 'vs_api_login_by_signature' );
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'signature' => $signature,
        ]);
    }
}
