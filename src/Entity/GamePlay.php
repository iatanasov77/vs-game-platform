<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ToggleableTrait;

/**
 * GamePlay Entity
 * 
 * Games Played in the Room (Example: In Bridge Belote One game finished when a team reach 151 points in Sore)
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
    
    /** @var GameRoom */
    #[ORM\ManyToOne(targetEntity: GameRoom::class, inversedBy: "playSessions")]
    #[ORM\JoinColumn(name: "game_room_id", referencedColumnName: "id", nullable: false)]
    private $gameRoom;
    
    /** @var array */
    #[ORM\Column(type: "json", nullable: true)]
    private $score;
    
    /** @var bool */
    #[ORM\Column(name: "active", type: "boolean", options: ["default" => 0])]
    protected $enabled = true;
    
    public function getId(): ?int
    {
        return $this->id;
    }
    
    public function getGameRoom(): ?GameRoom
    {
        return $this->gameRoom;
    }
    
    public function setGameRoom( GameRoom $gameRoom ): self
    {
        $this->gameRoom = $gameRoom;
        
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