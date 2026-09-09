<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Component\Type\PlayerPosition;
use App\Component\Utils\Guid;
use App\Component\Manager\GameManagerInterface;
use App\Entity\GamePlayer;

class Player // implements PlayerInterface
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
    
    public static function MyPosition( GameManagerInterface $manager, GamePlayer $dbUser, PlayerPosition $position ): bool
    {
        //prevents someone with same game id, get someone elses side in the game.
        $player = $manager->Game->SouthPlayer;
        
        if ( $position == PlayerPosition::East ) {
            $player = $manager->Game->WhitePlayer;
        }
        
        return $dbUser != null && $dbUser->getId() == $player->Id;
    }
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Guid == GamePlayer::AiUser;
    }
    
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
}
