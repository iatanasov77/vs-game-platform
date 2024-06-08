<?php namespace App\Controller\GamePlatformAPI;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

use Vankosoft\ApplicationBundle\Component\Context\ApplicationContextInterface;

class DefaultController extends AbstractController
{
    use GlobalFormsTrait;

    /** @var ApplicationContextInterface */
    private $applicationContext;
    
    /** @var Environment */
    private $templatingEngine;

    /** @var ManagerRegistry **/
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $categoryRepository;
    
    /** @var RepositoryInterface */
    private $productRepository;
    
    public function __construct(
        ApplicationContextInterface $applicationContext,
        Environment $templatingEngine,
        ManagerRegistry $doctrine,
        RepositoryInterface $categoryRepository,
        RepositoryInterface $productRepository
    ) {
        $this->applicationContext   = $applicationContext;
        $this->templatingEngine     = $templatingEngine;

        $this->doctrine             = $doctrine;
        $this->categoryRepository   = $categoryRepository;
        $this->productRepository    = $productRepository;
    }
    
    public function index( Request $request ): Response
    {
        return new Response( $this->templatingEngine->render( $this->getTemplate(), [
            'shoppingCart'      => $this->getShoppingCart( $request ),
            'categories'        => $this->categoryRepository->findAll(),
            'latestProducts'    => $this->productRepository->findAll(),
        ] ) );
    }
    
    protected function getTemplate(): string
    {
        $template   = 'game-platform-api/Pages/Dashboard/index.html.twig';
        
        $appSettings    = $this->applicationContext->getApplication()->getSettings();
        if ( ! $appSettings->isEmpty() && $appSettings[0]->getTheme() ) {
            $template   = 'Pages/Dashboard/index.html.twig';
        }
        
        return $template;
    }
}
