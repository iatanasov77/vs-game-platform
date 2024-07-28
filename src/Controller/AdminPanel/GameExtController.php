<?php namespace App\Controller\AdminPanel;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sylius\Bundle\ResourceBundle\Doctrine\ORM\EntityRepository;
use Vankosoft\ApplicationBundle\Controller\Traits\TaxonomyTreeDataTrait;
use Vankosoft\ApplicationBundle\Repository\TaxonomyRepository;

class GameExtController extends AbstractController
{
    use TaxonomyTreeDataTrait;
    
    public function __construct(
        TaxonomyRepository $taxonomyRepository,
        EntityRepository $gamesRepository,
    ) {
        $this->taxonomyRepository   = $taxonomyRepository;
        $this->gamesRepository      = $gamesRepository;
    }
    
    public function easyuiComboTreeWithSelectedSource( $taxonomyId, $gameId, Request $request ): Response
    {
        return new JsonResponse( $this->easyuiComboTreeData( $taxonomyId, $this->getSelectedCategoryTaxons( $gameId ) ) );
    }
    
    protected function getSelectedCategoryTaxons( $gameId ): array
    {
        $selected   = [];
        $game       = $this->gamesRepository->find( $gameId );
        if ( $game ) {
            $selected[] = $game->getCategory()->getTaxon()->getId();
        }
        
        return $selected;
    }
}