<?php namespace App\Repository;

use Vankosoft\ApplicationBundle\Repository\TaxonDescendentRepository;

use Sylius\Bundle\ResourceBundle\Doctrine\ORM\ResourceRepositoryTrait;
use Vankosoft\ApplicationBundle\Repository\Traits\TaxonRepositoryTrait;

class GameCategoryRepository extends TaxonDescendentRepository
{
    use ResourceRepositoryTrait;
    use TaxonRepositoryTrait;
}
