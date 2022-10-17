<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Vankosoft\ApplicationBundle\Model\Interfaces\TaxonInterface;

/**
 * @ORM\Table(name="VSGP_GamesCategories")
 * @ORM\Entity
 */
class GameCategory implements ResourceInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @var TaxonInterface
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Application\Taxon", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="taxon_id", referencedColumnName="id", nullable=false)
     */
    private $taxon;
    
    /**
     * @var GameCategory
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\GameCategory", inversedBy="children", cascade={"all"})
     */
    private $parent;
    
    /**
     * @var Collection|GameCategory[]
     *
     * @ORM\OneToMany(targetEntity="App\Entity\GameCategory", mappedBy="parent")
     */
    private $children;
    
    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Game", mappedBy="category", orphanRemoval=true)
     * @ORM\OrderBy({"position" = "ASC"})
     */
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
    
    /**
     * {@inheritdoc}
     */
    public function getTaxon(): ?TaxonInterface
    {
        return $this->taxon;
    }
    
    /**
     * {@inheritdoc}
     */
    public function setTaxon( ?TaxonInterface $taxon ): self
    {
        $this->taxon = $taxon;
        
        return $this;
    }
    
    public function getName()
    {
        return $this->taxon ? $this->taxon->getName() : '';
    }
    
    public function setName( string $name ): self
    {
        if ( ! $this->taxon ) {
            // Create new taxon into the controller and set the properties passed from form
            return $this;
        }
        $this->taxon->setName( $name );
        
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
