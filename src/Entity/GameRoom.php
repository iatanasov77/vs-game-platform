<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Entity\UserManagement\User;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GameRooms")]
class GameRoom implements ResourceInterface
{
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var Game */
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: "rooms")]
    private $game;
    
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: "gameRooms", indexBy: "id")]
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
     * @return Collection|User[]
     */
    public function getPlayers(): Collection
    {
        return $this->players;
    }
    
    public function addPlayer( User $player ): self
    {
        if ( ! $this->players->contains( $player ) ) {
            $this->players[] = $player;
        }
        
        return $this;
    }
    
    public function removePlayer( User $player ): self
    {
        if ( $this->players->contains( $player ) ) {
            $this->players->removeElement( $player );
        }
        
        return $this;
    }
}