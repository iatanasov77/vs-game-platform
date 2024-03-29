<?php namespace App\Controller\Chess;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;

class DefaultController extends AbstractController
{
    /** @var ApplicationContextInterface */
    private $applicationContext;
    
    /** @var Environment */
    private $templatingEngine;
    
    /** @var EntityRepository */
    private $gamesRepository;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        EntityRepository $gamesRepository
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->gamesRepository      = $gamesRepository;
    }
    
    public function index( Request $request ): Response
    {
        $gameSlug   = 'chess';
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        
        return new Response( $this->templatingEngine->render( $this->getTemplate(), ['game' => $game] ) );
    }
    
    protected function getTemplate(): string
    {
        $template   = 'chess/Pages/Chess/index.html.twig';
        
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( ! $appSettings->isEmpty() && $appSettings[0]->getTheme() ) {
            $template   = 'Pages/Chess/index.html.twig';
        }
        
        return $template;
    }
}
