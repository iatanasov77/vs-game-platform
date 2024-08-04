<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GameRooms")]
class GameRoom implements ResourceInterface
{
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255)]
    private $name;
    
    /** @var Game */
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: "rooms")]
    private $game;
    
    #[ORM\ManyToMany(targetEntity: GamePlayer::class, inversedBy: "rooms", indexBy: "id")]
    #[ORM\JoinTable(name: "VSGP_GameRooms_Players")]
    #[ORM\JoinColumn(name: "game_id", referencedColumnName: "id")]
    #[ORM\InverseJoinColumn(name: "player_id", referencedColumnName: "id")]
    private $players;
    
    public function __construct()
    {
        $this->players  = new ArrayCollection();
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
}