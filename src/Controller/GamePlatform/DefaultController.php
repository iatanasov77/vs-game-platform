<?php namespace App\Controller\GamePlatform;

use App\Controller\Application\DefaultController as BaseDefaultController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Environment;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\Utils\Keys;

class DefaultController extends BaseDefaultController
{
    /** @var ApplicationContextInterface */
    private $applicationContext;
    
    /** @var Environment */
    private $templatingEngine;
    
    /** @var EntityRepository */
    private $gcRepository;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        EntityRepository $gcRepository
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->gcRepository         = $gcRepository;
    }
    
    public function index( Request $request ): Response
    {
        return new Response( $this->templatingEngine->render( $this->getTemplate(), [
            'gameCategories'    => $this->gcRepository->findAll(),
        ]));
    }
    
    public function getGameCookie( Request $request ): Response
    {
        return new JsonResponse([
            'status'        => Status::STATUS_OK,
            'gameCookie'    => $request->cookies->get( Keys::GAME_ID_KEY ),
        ]);
    }
    
    protected function getTemplate(): string
    {
        $template   = 'game-platform/Pages/Dashboard/index.html.twig';
        
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( ! $appSettings->isEmpty() && $appSettings[0]->getTheme() ) {
            $template   = 'Pages/Dashboard/index.html.twig';
        }
        
        return $template;
    }
}
