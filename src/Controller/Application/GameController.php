<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Twig\Environment;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;
use Vankosoft\ApiBundle\Exception\ApiLoginException;
use Vankosoft\ApplicationBundle\Component\Status;

class GameController extends AbstractController
{
    /** @var ApplicationContextInterface */
    protected $applicationContext;
    
    /** @var Environment */
    protected $templatingEngine;
    
    /** @var EntityRepository */
    protected $gamesRepository;
    
    /** @var HttpClientInterface */
    protected $httpClient;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        EntityRepository $gamesRepository,
        HttpClientInterface $httpClient
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->gamesRepository      = $gamesRepository;
        $this->httpClient           = $httpClient;
    }
    
    protected function getTemplate( string $gameSlug, string $template ): string
    {
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( $appSettings->isEmpty() || ! $appSettings[0]->getTheme() ) {
            $template   = $gameSlug . '/' . $template;
        }
        
        return $template;
    }
    
    protected function getVerifySignature(): ?string
    {
        try {
            $signature  = null;
            //$response   = $this->httpClient->request( 'GET', 'http://api.game-platform.lh/api/get-verify-signature' );
            
            if ( isset( $response ) && isset( $response['status'] ) && $response['status'] == Status::STATUS_OK ) {
                $signature  = $response['signature'];
            }
            
            return $signature;
        }
        catch ( ClientException $e ) {
            return $signature;
        }
        catch ( JWTEncodeFailureException $e ) {
            throw new ApiLoginException( 'JWTEncodeFailureException: ' . $e->getMessage() );
        }
    }
}
