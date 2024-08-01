<?php namespace App\Controller\GamePlatformNew;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class PagesController extends AbstractController
{
    use GlobalFormsTrait;
    
    /** @var ManagerRegistry **/
    private $pagesRepository;
    
    public function __construct(
        EntityRepository $pagesRepository
    ) {
        $this->pagesRepository  = $pagesRepository;
    }
    
    public function aboutApplication( Request $request ): Response
    {
        $params = [
            'page'  => $this->pagesRepository->findBySlug( 'about-application' ),
        ];
        return $this->render( '@VSCms/Pages/Pages/show.html.twig', $params );
    }
}
