<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Twig\Environment;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;
use Vankosoft\ApiBundle\Security\ApiManager;

class GameController extends AbstractController
{
    /** @var ApplicationContextInterface */
    protected $applicationContext;
    
    /** @var Environment */
    protected $templatingEngine;
    
    /** @var EntityRepository */
    protected $gamesRepository;
    
    /** @var ApiManager */
    protected $apiManager;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        EntityRepository $gamesRepository,
        ApiManager $apiManager
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;
        $this->gamesRepository      = $gamesRepository;
        $this->apiManager           = $apiManager;
    }
    
    protected function getTemplate( string $gameSlug, string $template ): string
    {
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( $appSettings->isEmpty() || ! $appSettings[0]->getTheme() ) {
            $template   = $gameSlug . '/' . $template;
        }
        
        return $template;
    }
}
