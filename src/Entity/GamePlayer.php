<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UserManagement\User;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamePlayers")]
class GamePlayer implements ResourceInterface
{
    const TYPE_COMPUTER = 'computer';
    const TYPE_USER = 'user';
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var User */
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "player", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id", nullable: true)]
    private $user;
    
    #[ORM\ManyToMany(targetEntity: GameRoom::class, mappedBy: "players", indexBy: "id")]
    private $rooms;
    
    /** @var string */
    #[ORM\Column(type: "string", columnDefinition: "ENUM('computer', 'user')")]
    private $type;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255)]
    private $name;
    
    public function __construct()
    {
        $this->rooms    = new ArrayCollection();
    }
    
    public function getId(): ?int
    {
        return $this->id;
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
    
    public function getName(): ?string
    {
        return $this->name;
    }
    
    public function setName( string $name ): self
    {
        $this->name = $name;
        
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
     * @return Collection|GameRoom[]
     */
    public function getRooms(): Collection
    {
        return $this->rooms;
    }
    
    public function addRoom( GameRoom $room ): self
    {
        if ( ! $this->rooms->contains( $room ) ) {
            $this->rooms[] = $room;
        }
        
        return $this;
    }
    
    public function removeRoom( GameRoom $room ): self
    {
        if ( $this->rooms->contains( $room ) ) {
            $this->rooms->removeElement( $room );
        }
        
        return $this;
    }
}