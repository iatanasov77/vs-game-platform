<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vankosoft\ApplicationBundle\Model\Interfaces\TaxonDescendentInterface;
use Vankosoft\ApplicationBundle\Model\Traits\TaxonDescendentEntity;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamesCategories")]
class GameCategory implements ResourceInterface, TaxonDescendentInterface
{
    use TaxonDescendentEntity;
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var GameCategory */
    #[ORM\ManyToOne(targetEntity: GameCategory::class, inversedBy: "children", cascade: ["all"])]
    private $parent;
    
    /** @var Collection | GameCategory[] */
    #[ORM\OneToMany(targetEntity: GameCategory::class, mappedBy: "parent", cascade: ["persist", "remove"], orphanRemoval: true)]
    private $children;
    
    /** @var Collection | Game[] */
    #[ORM\OneToMany(targetEntity: Game::class, mappedBy: "category", indexBy: "id", orphanRemoval: true)]
    #[ORM\OrderBy(["position" => "ASC"])]
    private $games;
    
    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->games    = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    /**
     * {@inheritdoc}
     */
    public function getParent(): ?GameCategory
    {
        return $this->parent;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setParent( ?GameCategory $parent ): self
    {
        $this->parent = $parent;
        
        return $this;
    }
    
    public function getChildren() : Collection
    {
        //return $this->children;
        return $this->taxon->getChildren();
    }
    
    /**
     * @return Collection|Game[]
     */
    public function getGames(): Collection
    {
        return $this->games;
    }
    
    public function addGame( Game $game ): self
    {
        if ( ! $this->games->contains( $game ) ) {
            $this->games[] = $game;
            $game->addCategory( $this );
        }
        
        return $this;
    }
    
    public function removeGame( Game $game ): self
    {
        if ( $this->games->contains( $game ) ) {
            $this->games->removeElement( $game );
            $game->removeCategory( $this );
        }
        
        return $this;
    }
    
    public function __toString()
    {
        return $this->taxon ? $this->taxon->getName() : '';
    }
    
    public function getNameTranslated( string $locale )
    {
        return $this->taxon ? $this->taxon->getTranslation( $locale )->getName() : '';
    }
}
