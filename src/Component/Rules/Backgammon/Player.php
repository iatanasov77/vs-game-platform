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
    
    /**
     * Do not map this to the dto. Opponnents id should never be revealed to anyone else.
     * 
     * @var Guid
     */
    public $Id;
    
    /** @var bool */
    public $FirstMoveMade;
    
    public function __toString(): string
    {
        $playerColor;
        switch ( $this->PlayerColor->value ) {
            case 0:
                $playerColor = 'Black';
                break;
            case 1:
                $playerColor = 'White';
                break;
            default:
                $playerColor = 'Neither';   
        }
        return $playerColor . " player";
    }
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Id == GamePlayer::AiUser;
    }
}
