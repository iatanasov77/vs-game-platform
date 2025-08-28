<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation( "ORM\MappedSuperclass" )
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("ORM\Column")
 */
#[ORM\Entity]
#[ORM\Table(name: "VSGP_TempPlayers")]
class TempPlayer implements ResourceInterface
{
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $guid;
    
    /** @var GamePlayer */
    #[ORM\ManyToOne(targetEntity: GamePlayer::class, inversedBy: "gamePlayers", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "player_id", referencedColumnName: "id")]
    private $player;
    
    /** @var GamePlay */
    #[ORM\ManyToOne(targetEntity: GamePlay::class, inversedBy: "gamePlayers", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "game_id", referencedColumnName: "id")]
    private $game;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 255)]
    private $name;
    
    /** @var string */
    #[ORM\Column(type: "string", columnDefinition: "ENUM('black', 'white')", nullable: true)]
    private $color;
    
    /** @var string */
    #[ORM\Column(type: "string", columnDefinition: "ENUM('north', 'south', 'east', 'west')", nullable: true)]
    private $position;
    
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
    
    public function getPlayer(): ?GamePlayer
    {
        return $this->player;
    }
    
    public function setPlayer( GamePlayer $player ): self
    {
        $this->player = $player;
        
        return $this;
    }
    
    public function getGame(): ?GamePlay
    {
        return $this->game;
    }
    
    public function setGame( GamePlay $game ): self
    {
        $this->game = $game;
        
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
    
    public function getColor(): ?string
    {
        return $this->color;
    }
    
    public function setColor( string $color ): self
    {
        $this->color = $color;
        
        return $this;
    }
    
    public function getPosition(): ?string
    {
        return $this->position;
    }
    
    public function setPosition( string $position ): self
    {
        $this->position = $position;
        
        return $this;
    }
    
    public function getType(): ?string
    {
        return $this->player->getType();
    }
}