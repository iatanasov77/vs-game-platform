<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UserManagement\User;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamePlayers")]
class GamePlayer implements ResourceInterface
{
    const AiUser = "5";
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $guid;
    
    /** @var User */
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "player", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private $user;
    
    /** @var string */
    #[ORM\Column(type: "string", columnDefinition: "ENUM('computer', 'user')")]
    private $type;
    
    /** @var Collection | TempPlayer[] */
    #[ORM\OneToMany(targetEntity: TempPlayer::class, mappedBy: "player", indexBy: "id", cascade: ["persist"])]
    private $gamePlayers;
    
    /** @var int */
    #[ORM\Column(type: "integer", nullable: true)]
    private $elo;
    
    /** @var int */
    #[ORM\Column(name: "game_count", type: "integer", nullable: true)]
    private $gameCount;
    
    /** @var int */
    #[ORM\Column(type: "integer", nullable: true)]
    private $gold;
    
    /** @var \DateTimeInterface */
    #[ORM\Column(name: "last_free_gold", type: Types::DATETIME_IMMUTABLE, nullable: true)]
    private $lastFreeGold;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255, nullable: true)]
    private $photoUrl;
    
    /** @var bool */
    #[ORM\Column(name: "show_photo", type: "boolean", options: ["default" => 0])]
    private $showPhoto  = false;
    
    public function __construct()
    {
        $this->gamePlayers  = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getGuid(): ?string
    {
        return $this->guid;
    }
    
    public function setGuid( string $guid ): self
    {
        $this->guid = $guid;
        
        return $this;
    }
    
    public function getType(): ?string
    {
        return $this->type;
    }
    
    public function setType( string $type ): self
    {
        $this->type = $type;
        
        return $this;
    }
    
    public function getUser(): ?User
    {
        return $this->user;
    }
    
    public function setUser( User $user ): self
    {
        $this->user = $user;
        
        return $this;
    }
    
    /**
     * @return Collection|TempPlayer[]
     */
    public function getGamePlayers(): Collection
    {
        return $this->gamePlayers;
    }
    
    public function addGamePlayer( TempPlayer $gamePlayer ): self
    {
        if ( ! $this->gamePlayers->contains( $gamePlayer ) ) {
            $this->gamePlayers[] = $gamePlayer;
        }
        
        return $this;
    }
    
    public function removeGamePlayer( TempPlayer $gamePlayer ): self
    {
        if ( $this->gamePlayers->contains( $gamePlayer ) ) {
            $this->gamePlayers->removeElement( $gamePlayer );
        }
        
        return $this;
    }
    
    public function getElo(): ?int
    {
        return $this->elo;
    }
    
    public function setElo( int $elo ): self
    {
        $this->elo = $elo;
        
        return $this;
    }
    
    public function getGameCount(): ?int
    {
        return $this->gameCount;
    }
    
    public function setGameCount( int $gameCount ): self
    {
        $this->gameCount = $gameCount;
        
        return $this;
    }
    
    public function getGold(): ?int
    {
        return $this->gold;
    }
    
    public function setGold( int $gold ): self
    {
        $this->gold = $gold;
        
        return $this;
    }
    
    public function getLastFreeGold(): ?\DateTimeInterface
    {
        return $this->lastFreeGold;
    }
    
    public function setLastFreeGold(\DateTimeInterface $lastFreeGold): self
    {
        $this->lastFreeGold = $lastFreeGold;
        
        return $this;
    }
    
    public function getPhotoUrl(): ?int
    {
        return $this->photoUrl;
    }
    
    public function setPhotoUrl( string $photoUrl ): self
    {
        $this->photoUrl = $photoUrl;
        
        return $this;
    }
    
    public function getShowPhoto(): ?int
    {
        return $this->showPhoto;
    }
    
    public function setShowPhoto( bool $showPhoto ): self
    {
        $this->showPhoto = $showPhoto;
        
        return $this;
    }
    
    public function getName(): ?string
    {
        return $this->user ? $this->user->getUsername() : 'AI';
    }
}