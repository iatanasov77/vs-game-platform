<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use App\Entity\Application\Application;

/**
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation( "ORM\MappedSuperclass" )
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("ORM\Column")
 */
#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamePlatformApplications")]
class GamePlatformApplication implements ResourceInterface
{
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var Application */
    #[ORM\OneToOne(targetEntity: Application::class, inversedBy: "gamePlatformApplication")]
    #[ORM\JoinColumn(name: "application_id", referencedColumnName: "id")]
    private $application;
    
    /** @var GamePlatformSettings */
    #[ORM\ManyToOne(targetEntity: "GamePlatformSettings", inversedBy: "gamePlatformApplication")]
    #[ORM\JoinColumn(name: "settings_id", referencedColumnName: "id")]
    private $settings;
    
    public function getId()
    {
        return $this->id;
    }
    
    public function getApplication()
    {
        return $this->application;
    }
    
    public function setApplication($application)
    {
        $this->application  = $application;
        
        return $this;
    }
    
    public function getSettings()
    {
        return $this->settings;
    }
    
    public function setSettings($settings)
    {
        $this->settings  = $settings;
        
        return $this;
    }
}