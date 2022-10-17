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
     * @ORM\Column(name="original_name", type="string", length=255, nullable=false, options={"comment": "The Original Name of the File."})
     */
    protected $originalName;
    
    public function getOriginalName(): string
    {
        return $this->originalName;
    }
    
    public function setOriginalName( string $originalName ): self
    {
        $this->originalName = $originalName;
        
        return $this;
    }
}
