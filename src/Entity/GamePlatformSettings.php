<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation( "ORM\MappedSuperclass" )
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("ORM\Column")
 */
#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamePlatformSettings")]
class GamePlatformSettings implements ResourceInterface
{
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var GamePlatformApplication */
    #[ORM\OneToMany(targetEntity: "GamePlatformApplication", mappedBy: "settings")]
    private $gamePlatformApplication;
    
    /** @var string */
    #[ORM\Column(name: "settings_key", type: "string", length: 32)]
    private $settingsKey;
    
    /** @var integer */
    #[ORM\Column(name: "timeout_between_players", type: "integer")]
    private $timeoutBetweenPlayers;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getGamePlatformApplication()
    {
        return $this->gamePlatformApplication;
    }
    
    public function setGamePlatformApplication($gamePlatformApplication)
    {
        $this->gamePlatformApplication  = $gamePlatformApplication;
        
        return $this;
    }
    
    public function getSettingsKey()
    {
        return $this->settingsKey;
    }
    
    public function setSettingsKey($settingsKey)
    {
        $this->settingsKey  = $settingsKey;
        
        return $this;
    }
    
    public function getTimeoutBetweenPlayers()
    {
        return $this->timeoutBetweenPlayers;
    }
    
    public function setTimeoutBetweenPlayers($timeoutBetweenPlayers)
    {
        $this->timeoutBetweenPlayers  = $timeoutBetweenPlayers;
        
        return $this;
    }
}