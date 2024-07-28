<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Vankosoft\CmsBundle\Model\FileInterface;
use App\Entity\Application\Translation;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_Games")]
#[Gedmo\TranslationEntity(class: Translation::class)]
class Game implements ResourceInterface
{
    use ToggleableTrait;
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var bool */
    #[ORM\Column(type: "boolean", options: ["default" => 0])]
    protected $enabled = true;
    
    /** @var string */
    #[Gedmo\Locale]
    private $locale;
    
    /** @var GameCategory */
    #[ORM\ManyToOne(targetEntity: "GameCategory", inversedBy: "games", fetch: "EAGER")]
    #[Gedmo\SortableGroup]
    private $category;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255)]
    #[Gedmo\Translatable]
    private $title;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Gedmo\Slug(fields: ["title"])]
    #[Gedmo\Translatable]
    private $slug;
    
    /**
     * @Gedmo\SortablePosition
     * @ORM\Column(name="position", type="integer")
     */
    /** @var int */
    #[ORM\Column(type: "integer")]
    #[Gedmo\SortablePosition]
    private $position;
    
    #[ORM\OneToOne(targetEntity: GamePicture::class, mappedBy: "owner", cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "picture_id", referencedColumnName: "id", nullable: true)]
    private $picture;
    
    /** @var string */
    #[ORM\Column(name: "game_url", type: "string", length: 255, nullable: true)]
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
    
    public function getPicture(): ?GamePicture
    {
        return $this->picture;
    }
    
    public function setPicture( ?GamePicture $picture ): self
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
