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
    
    public function getTranslationsAction( $locale, Request $request ): Response
    {
        switch ( $locale ) {
            case 'bg_BG':
                $translations   = $this->getBulgarianTranslations();
                break;
            default:
                $translations   = $this->getEnglishTranslations();
        }
        
        return new JsonResponse( $translations );
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
    
    private function getEnglishTranslations():array
    {
        return [
            'game_board.statistics.we'  => 'We',
            'game_board.statistics.you' => 'You',
        ];
    }
    
    private function getBulgarianTranslations():array
    {
        return [
            'game_board.statistics.we'  => 'Ние',
            'game_board.statistics.you' => 'Вие',
        ];
    }
}
