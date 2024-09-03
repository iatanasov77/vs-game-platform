<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\ToggleableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GameRooms")]
class GameRoom implements ResourceInterface
{
    use ToggleableTrait;
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255)]
    private $name;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255, unique: true)]
    #[Gedmo\Slug(fields: ["name"])]
    private $slug;
    
    /** @var Game */
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: "rooms")]
    private $game;
    
    /** @var Collection | GamePlayer[] */
    #[ORM\ManyToMany(targetEntity: GamePlayer::class, inversedBy: "rooms", indexBy: "id")]
    #[ORM\JoinTable(name: "VSGP_GameRooms_Players")]
    #[ORM\JoinColumn(name: "game_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "player_id", referencedColumnName: "id")]
    private $players;
    
    /** @var Collection | GamePlay[] */
    #[ORM\OneToMany(targetEntity: GamePlay::class, mappedBy: "gameRoom", cascade: ["persist", "remove"], orphanRemoval: true)]
    private $playSessions;
    
    /** @var bool */
    #[ORM\Column(name: "is_playing", type: "boolean", options: ["default" => 0])]
    protected $enabled = true;
    
    public function __construct()
    {
        $this->players      = new ArrayCollection();
        $this->playSessions = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName( string $name ): self
    {
        $this->name = $name;
        
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
    
    public function getGame(): ?Game
    {
        return $this->game;
    }
    
    public function setGame( Game $game ): self
    {
        $this->game = $game;
        
        return $this;
    }
    
    /**
     * @return Collection|GamePlayer[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }
    
    public function addPlayer( GamePlayer $player ): self
    {
        if ( ! $this->players->contains( $player ) ) {
            $this->players[] = $player;
        }
        
        return $this;
    }
    
    public function removePlayer( GamePlayer $player ): self
    {
        if ( $this->players->contains( $player ) ) {
            $this->players->removeElement( $player );
        }
        
        return $this;
    }
    
    /**
     * @return Collection|GamePlay[]
     */
    public function getPlaySessions(): Collection
    {
        return $this->playSessions;
    }
    
    public function addPlaySession( GamePlay $playSession ): self
    {
        if ( ! $this->playSessions->contains( $playSession ) ) {
            $this->playSessions[] = $playSession;
        }
        
        return $this;
    }
    
    public function removePlaySession( GamePlay $playSession ): self
    {
        if ( $this->playSessions->contains( $playSession ) ) {
            $this->playSessions->removeElement( $playSession );
        }
        
        return $this;
    }
    
    public function isPlaying(): bool
    {
        return $this->isEnabled();
    }
    
    public function setIsPlaying( bool $isPlaying ): self
    {
        $this->setEnabled( $isPlaying );
        
        return $this;
    }
}