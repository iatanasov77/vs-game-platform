<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vankosoft\CmsBundle\Model\File;

#[ORM\Entity]
#[ORM\Table(name: "VSGP_GamePictures")]
class GamePicture extends File
{
    /** @var Game */
    #[ORM\OneToOne(targetEntity: Game::class, inversedBy: "picture", cascade: ["persist", "remove"], orphanRemoval: true)]
    #[ORM\JoinColumn(name: "owner_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    protected $owner;
    
    public function getGame()
    {
        return $this->owner;
    }
    
    public function setGame( Game $game ): self
    {
        $this->setOwner( $game);
        
        return $this;
    }
}
