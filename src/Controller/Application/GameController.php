<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Lexik\Bundle\JWTAuthenticationBundle\Exception\JWTEncodeFailureException;
use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;
use Vankosoft\ApiBundle\Exception\ApiLoginException;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\GamePlatform;
use App\Entity\Game;

/**
 * Extending From All Game Controllers
 */
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
    
    /** @var TranslatorInterface */
    protected $translator;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        EntityRepository $gamesRepository,
        HttpClientInterface $httpClient,
        TranslatorInterface $translator
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->gamesRepository      = $gamesRepository;
        $this->httpClient           = $httpClient;
        $this->translator           = $translator;
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
        $signedUrl  = null;
        $appHost    = $this->getParameter( 'vankosoft_host' );
        
        try {
            $response       = $this->httpClient->request( 'GET', \sprintf( 'http://api.%s/api/get-verify-signature', $appHost ) );
            $decodedPayload = $response->toArray( false );
            //echo '<pre>'; var_dump( $decodedPayload ); die;
            
            if ( isset( $decodedPayload['status'] ) && $decodedPayload['status'] == Status::STATUS_OK ) {
                $signedUrl  = $decodedPayload['signedUrl'];
            }
            
            return $signedUrl;
        }
        catch ( ClientException $e ) {
            return $signedUrl;
        }
        catch ( JWTEncodeFailureException $e ) {
            throw new ApiLoginException( 'JWTEncodeFailureException: ' . $e->getMessage() );
        }
    }
    
    protected function showGameStatus( Request $request, Game $game ): void
    {
        if ( $request->hasSession() ) {
            switch ( $game->getStatus() ) {
                case GamePlatform::GAME_STATUS_IN_DEVELOPEMENT:
                    $message = $this->translator->trans( 'game_platform.game_status.in_developement', [], 'GamePlatform' );
                    break;
                case GamePlatform::GAME_STATUS_IN_DEVELOPEMENT_BUT:
                    $message = $this->translator->trans( 'game_platform.game_status.in_developement_but', [], 'GamePlatform' );
                    break;
                default:
                    $message = $this->translator->trans( 'game_platform.game_status.not_implemented', [], 'GamePlatform' );
            }
            
            $request->getSession()->getFlashBag()->add( 'notice', $message );
        }
    }
}
