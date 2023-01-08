<?php namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vankosoft\CmsBundle\Model\File;

/**
 * @ORM\Table(name="VSGP_GamePictures")
 * @ORM\Entity
 */
class GamePicture extends File
{
    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Game", inversedBy="picture", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=false, onDelete="CASCADE")
     */
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
