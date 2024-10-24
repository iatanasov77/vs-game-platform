<?php namespace App\Component\Rules\Backgammon;

use App\Component\Type\PlayerColor;
use App\Component\System\Guid;
use App\Entity\GamePlayer;

class Player
{
    /** @var string */
    public $Name;
    
    /** @var PlayerColor */
    public $PlayerColor;
    
    /** @var int */
    public $PointsLeft;
    
    /** @var string */
    public $Photo;
    
    /** @var int */
    public $Gold;
    
    /** @var int */
    public $Elo;
    
    /// <summary>
    /// Do not map this to the dto. Opponnents id should never be revealed to anyone else.
    /// </summary>
    /** @var Guid */
    public $Id;
    
    /** @var bool */
    public $FirstMoveMade;
    
    //     public function __toString(): string
    //     {
    //         return $this->PlayerColor->value . " player";
    //     }
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Id == GamePlayer::AiUser;
    }
}
