<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Vankosoft\CmsBundle\Model\FileInterface;

/**
 * @Gedmo\TranslationEntity(class="App\Entity\Application\Translation")
 * @ORM\Table(name="VSGP_Games")
 * @ORM\Entity
 */
class Game implements ResourceInterface
{
    use ToggleableTrait;
    
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;
    
    /**
     * @var bool
     *
     * @ORM\Column(type="boolean", options={"default":"0"})
     */
    protected $enabled = true;
    
    /**
     * @var string
     *
     * @Gedmo\Locale
     */
    private $locale;
    
    /**
     * @Gedmo\SortableGroup
     * @ORM\ManyToOne(targetEntity="App\Entity\GameCategory", inversedBy="games")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     */
    private $category;
    
    /**
     * @var string
     *
     * @Gedmo\Translatable
     * @ORM\Column(name="title", type="string", length=255, nullable=false)
     */
    private $title;
    
    /**
     * Use Slug for Subdomain of Game Url
     * 
     * @Gedmo\Slug(fields={"title"})
     * @ORM\Column(name="slug", type="string", length=255, nullable=false, unique=true)
     */
    private $slug;
    
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    private $position;
    
    /**
     * @var FileInterface|null
     *
     * @ORM\OneToOne(targetEntity="App\Entity\GamePicture", cascade={"all"}, orphanRemoval=true)
     * @ORM\JoinColumn(name="picture_id", referencedColumnName="id", nullable=true)
     */
    private $picture;
    
    /**
     * @var string
     *
     * @ORM\Column(name="game_url", type="string", length=255, nullable=true)
     */
    private $gameUrl;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getCategory(): ?GameCategory
    {
        return $this->category;
    }
    
    public function setCategory(?GameCategory $category): self
    {
        $this->category = $category;
        
        return $this;
    }
    
    public function getTitle(): ?string
    {
        return $this->title;
    }
    
    public function setTitle( $title )
    {
        $this->title = $title;
        
        return $this;
    }
    
    public function getSlug(): ?string
    {
        return $this->slug;
    }
    
    public function setSlug( $slug )
    {
        $this->slug = $slug;
        
        return $this;
    }
    
    public function getPosition()
    {
        return $this->position;
    }
    
    public function setPosition( $position ): self
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function getPicture(): ?FileInterface
    {
        return $this->picture;
    }
    
    public function setPicture( ?FileInterface $picture ): self
    {
        $picture->setOwner( $this );
        $this->picture  = $picture;
        
        return $this;
    }
    
    public function getGameUrl()
    {
        return $this->gameUrl;
    }
    
    public function setGameUrl($gameUrl)
    {
        $this->gameUrl = $gameUrl;
        
        return $this;
    }
    
    public function getTranslatableLocale()
    {
        return $this->locale;
    }
    
    public function getLocale()
    {
        return $this->locale;
    }
    
    public function setTranslatableLocale( $locale ): self
    {
        $this->locale = $locale;
        
        return $this;
    }
}
