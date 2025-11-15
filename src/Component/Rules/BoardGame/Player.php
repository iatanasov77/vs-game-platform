<?php namespace App\Component\Rules\BoardGame;

use App\Component\Type\PlayerColor;
use App\Component\Utils\Guid;
use App\Entity\GamePlayer;

class Player
{
    /** @var int */
    public $Id;
    
    /** @var string */
    public $Name;
    
    /** @var PlayerColor */
    public $PlayerColor;
    
    /** @var int */
    public $PointsLeft = 0;
    
    /** @var string */
    public $Photo;
    
    /** @var int */
    public $Gold;
    
    /**
     * Backgammon ELO is a player rating system similar to the one used in chess, 
     * which assigns a numerical score to players based on their performance in rated matches. 
     * A higher ELO indicates a stronger player, and your rating increases with wins 
     * and decreases with losses, with larger changes for upsets or defeats against stronger opponents. 
     * While there isn't a single international backgammon ELO system, 
     * many online platforms use their own ELO-based ratings, 
     * which are used to match players of similar skill levels. 
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
        return $this->Guid == GamePlayer::AiUser;
    }
}
