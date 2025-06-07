<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\ToggleableTrait;
use App\Entity\UserManagement\User;

/**
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation( "ORM\MappedSuperclass" )
 * @Doctrine\Common\Annotations\Annotation\IgnoreAnnotation("ORM\Column")
 */
#[ORM\Entity]
#[ORM\Table(name: "VSGP_MercureConnections")]
class MercureConnection implements ResourceInterface
{
    use ToggleableTrait;
    
    /** @var int */
    #[ORM\Id, ORM\Column(type: "integer"), ORM\GeneratedValue(strategy: "IDENTITY")]
    private $id;
    
    /** @var User */
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: "mercureConnection", cascade: ["persist"])]
    #[ORM\JoinColumn(name: "user_id", referencedColumnName: "id")]
    private $user;
    
    /** @var bool */
    #[ORM\Column(name: "active", type: "boolean", options: ["default" => 0])]
    protected $enabled = true;
    
    public function getId(): ?int
    {
        return $this->id;
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