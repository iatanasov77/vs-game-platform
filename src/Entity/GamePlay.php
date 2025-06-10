<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Sylius\Component\Resource\Model\ResourceInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ToggleableTrait;

/**
 * GamePlay Entity
 * 
 * Games Played in the Room (Example: In Bridge Belote One game finished when a team reach 151 points in Sore)
 */
/**
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation( "ORM\MappedSuperclass" )
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("ORM\Column")
 */
#[ORM\Entity]
#[ORM\Table(name: "VSGP_GameSessions")]
class GamePlay implements ResourceInterface
{
    use TimestampableEntity;
    use ToggleableTrait;
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $guid;
    
    /** @var Game */
    #[ORM\ManyToOne(targetEntity: Game::class, inversedBy: "gameSessions")]
    private $game;
    
    /** @var Collection | TempPlayer[] */
    #[ORM\OneToMany(targetEntity: TempPlayer::class, mappedBy: "player", indexBy: "id")]
    private $gamePlayers;
    
    /** @var string */
    #[ORM\Column(type: "string", length: 40, nullable: true)]
    private $winner;
    
    /** @var array */
    #[ORM\Column(type: "json", nullable: true)]
    private $score;
    
    /** @var bool */
    #[ORM\Column(name: "active", type: "boolean", options: ["default" => 0])]
    protected $enabled = true;
    
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
    
    public function getGame(): Game
    {
        return $this->game;
    }
    
    public function setGame( Game $game ): self
    {
        $this->game = $game;
        
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
    
    public function getWinner(): ?string
    {
        return $this->winner;
    }
    
    public function setWinner( string $winner ): self
    {
        $this->winner = $winner;
        
        return $this;
    }
    
    public function getScore()
    {
        return $this->score;
    }
    
    public function setScore($score)
    {
        $this->score = $score;
        
        return $this;
    }
    
    public function isActive(): bool
    {
        return $this->isEnabled();
    }
    
    public function setActive( bool $active ): self
    {
        $this->setEnabled( $active );
        
        return $this;
    }
}