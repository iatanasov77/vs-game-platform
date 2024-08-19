<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;

class GamesController extends AbstractController
{
    /** @var EntityRepository */
    private $gcRepository;
    
    public function __construct(
        EntityRepository $gcRepository
    ) {
        $this->gcRepository = $gcRepository;
    }
    
    public function index( Request $request ): Response
    {
        return $this->render( 'Pages/Games/index.html.twig', [
            'gameCategories'    => $this->gcRepository->findAll(),
        ]);
    }
}