<?php namespace App\Controller\GamePlatform\SocialNetworks;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel\ErrorCorrectionLevelHigh;
use Endroid\QrCode\RoundBlockSizeMode\RoundBlockSizeModeMargin;
use Endroid\QrCode\Writer\PngWriter;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface as GoogleAuthenticatorTwoFactorInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Provider\Google\GoogleAuthenticatorInterface;

class QrCodeController extends AbstractController
{
    /** @var TokenStorageInterface **/
    private $tokenStorage;
    
    /** @var GoogleAuthenticatorInterface **/
    private $googleAuthenticator;
    
    public function __construct(
        TokenStorageInterface $tokenStorage,
        GoogleAuthenticatorInterface $googleAuthenticator
    ) {
        $this->tokenStorage         = $tokenStorage;
        $this->googleAuthenticator  = $googleAuthenticator;
    }
    
    public function displayGoogleAuthenticatorQrCode(): Response
    {
        $user = $this->tokenStorage->getToken()->getUser();
        if ( ! ( $user instanceof GoogleAuthenticatorTwoFactorInterface ) ) {
            throw new NotFoundHttpException( 'Cannot display QR code' );
        }
        
        return $this->displayQrCode( $this->googleAuthenticator->getQRContent( $user ) );
    }
    
    private function displayQrCode( string $qrCodeContent ): Response
    {
        $result = Builder::create()
                    ->writer( new PngWriter() )
                    ->writerOptions( [] )
                    ->data( $qrCodeContent )
                    ->encoding( new Encoding( 'UTF-8' ) )
                    ->errorCorrectionLevel( new ErrorCorrectionLevelHigh() )
                    ->size( 200 )
                    ->margin( 0 )
                    ->roundBlockSizeMode( new RoundBlockSizeModeMargin() )
                    ->build();
        
        return new Response( $result->getString(), 200, ['Content-Type' => 'image/png'] );
    }
}