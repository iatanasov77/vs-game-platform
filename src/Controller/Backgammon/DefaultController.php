<?php namespace App\Controller\Backgammon;

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
    
    public function index( $gameSlug, Request $request ): Response
    {
        $game   = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        
        return new Response( $this->templatingEngine->render( $this->getTemplate(), ['game' => $game] ) );
    }
    
    protected function getTemplate(): string
    {
        $template   = 'backgammon/Pages/Backgammon/index.html.twig';
        
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( ! $appSettings->isEmpty() && $appSettings[0]->getTheme() ) {
            $template   = 'Pages/Backgammon/index.html.twig';
        }
        
        return $template;
    }
}
