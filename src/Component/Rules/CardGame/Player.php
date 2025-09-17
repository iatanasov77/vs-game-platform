<?php namespace App\Component\Rules\CardGame;

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Utils\Guid;
use App\Entity\GamePlayer;

use App\Component\Rules\CardGame\Context\PlayerGetBidContext;
use App\Component\Rules\CardGame\Context\PlayerGetAnnouncesContext;
use App\Component\Rules\CardGame\Context\PlayerPlayCardContext;

use App\Component\Dto\Actions\PlayCardActionDto;

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
    
    /** @var Collection */
    public $Cards;
    
    public function __construct()
    {
        $this->Cards = new ArrayCollection();
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
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Guid == GamePlayer::AiUser;
    }
    
    public function GetBid( PlayerGetBidContext $context ): BidType
    {
        return BidType::Pass;
    }
    
    public function GetAnnounces( PlayerGetAnnouncesContext $context ): Collection
    {
        $availableAnnounces = $context->AvailableAnnounces;
            
        return $availableAnnounces;
    }
    
    public function PlayCard( PlayerPlayCardContext $context ): PlayCardActionDto
    {
        $action = new PlayCardActionDto();
        
        return $action;
    }
}
