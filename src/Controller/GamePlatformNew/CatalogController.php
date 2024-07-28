<?php namespace App\Controller\GamePlatformNew;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Vankosoft\CatalogBundle\Controller\CatalogController as BaseCatalogController;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;

class CatalogController extends BaseCatalogController
{
    use GlobalFormsTrait;
    
    /** @var RepositoryInterface */
    protected $productCategoryRepository;
    
    /** @var RepositoryInterface */
    protected $productRepository;
    
	/** @var int **/
    protected $latestProductsLimit;
	
    /** @var ManagerRegistry **/
    private $doctrine;
    
    public function __construct(
        RepositoryInterface $productCategoryRepository,
        RepositoryInterface $productRepository,
		int $latestProductsLimit,
        ManagerRegistry $doctrine
    ) {
        parent::__construct( $productCategoryRepository, $productRepository, $latestProductsLimit );
        
        $this->doctrine	= $doctrine;
    }
    
    public function latestProductsAction( Request $request ): Response
    {
        $products   = $this->productRepository->findBy( [], ['updatedAt' => 'DESC'], $this->latestProductsLimit );
        
        return $this->render( '@VSCatalog/Pages/Catalog/latest_products.html.twig', [
            'products'      => $products,
            'shoppingCart'  => $this->getShoppingCart( $request ),
        ]);
    }
    
    public function categoryProductsAction( $categorySlug, Request $request ): Response
    {
        $category   = $this->productCategoryRepository->findByTaxonCode( $categorySlug );
        $products   = $category->getProducts();
        
        return $this->render( '@VSCatalog/Pages/Catalog/category_products.html.twig', [
            'products'      => $products,
            'shoppingCart'  => $this->getShoppingCart( $request ),
        ]);
    }
    
    public function showProductAction( $categorySlug, $productSlug, Request $request ): Response
    {
        $product   = $this->productRepository->findOneBy( ['slug' => $productSlug] );
        
        return $this->render( '@VSCatalog/Pages/Catalog/show_product.html.twig', [
            'product'       => $product,
            'shoppingCart'  => $this->getShoppingCart( $request ),
        ]);
    }
}