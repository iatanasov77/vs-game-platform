<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;
use App\Component\System\Guid;
use App\Entity\GamePlayer;

class Player
{
    /** @var int */
    public $Id;
    
    /** @var string */
    public $Name;
    
    /** @var PlayerPosition */
    public $PlayerPosition;
    
    /** @var string */
    public $Photo;
    
    /** @var int */
    public $Gold;
    
    /**
     * Player rating system, which assigns a numerical score to players 
     * based on their performance in rated matches. 
     * 
     * @var int
     */
    public $Elo;
    
    /**
     * Do not map this to the dto. Opponnents id should never be revealed to anyone else.
     * 
     * @var Guid
     */
    public $Guid;
    
    /** @var bool */
    public $FirstMoveMade;
    
    public function __toString(): string
    {
        switch ( $this->PlayerPosition->value ) {
            case 0:
                $playerPosition = 'North';
                break;
            case 1:
                $playerPosition = 'South';
                break;
            case 2:
                $playerPosition = 'East';
                break;
            case 3:
                $playerPosition = 'West';
                break;
            default:
                $playerPosition = 'Neither';   
        }
        return $playerPosition . " player";
    }
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Guid == GamePlayer::AiUser;
    }
}
