<?php namespace App\Controller\GamePlatform;

use Symfony\Component\HttpFoundation\Request;
use Vankosoft\CatalogBundle\Controller\ProductController as BaseProductController;

class ProductController extends BaseProductController
{
    use GlobalFormsTrait;
    
    protected function customData( Request $request, $entity = null ): array
    {
        $customData = parent::customData( $request, $entity );
        
        $customData['shoppingCart'] = $this->getShoppingCart( $request );
        
        return $customData;
    }
}