<?php namespace App\Component\Manager\Games;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use React\Async;
use React\EventLoop\Loop;
use React\EventLoop\TimerInterface;
use Amp\DeferredCancellation;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Manager\CardGameManager;
use App\Component\Websocket\Client\WebsocketClientInterface;

use App\Component\Rules\CardGame\BridgeBeloteCard as Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\AI\EngineFactory as AiEngineFactory;
use App\Entity\GamePlayer;

// Types
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidTrump;
use App\Component\Type\AnnounceType;
use App\Component\Type\GameState;
use App\Component\Type\CardGameTeam;
use App\Component\Type\BridgeBeloteCardType;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\AnnounceMadeActionDto;

class SvaraGameManager extends CardGameManager
{
    public function ConnectAndListen( WebsocketClientInterface $webSocket, GamePlayer $dbUser, bool $playAi ): void
    {
        $this->logger->log( "Connecting Game Manager ...", 'GameManager' );
        if ( $this->Game->CurrentPlayer == PlayerPosition::South ) {
            
        } else {
            if ( $playAi ) {
                throw new \Exception( "Ai always plays as north. This is not expected" );
            }
            
            // West Player
            $this->Clients->set( PlayerPosition::West->value, $webSocket );
            $this->InitializePlayer( $dbUser, false, $this->Game->WestPlayer );
            
            $this->CreateDbGame();
            $this->StartGame();
            
            //$this->dispatchGameEnded();
        }
    }
    
    protected function DoBid( BidMadeActionDto $action ): void
    {
        
    }
    
    protected function PlayCard( PlayCardActionDto $action ): void
    {
        
    }
    
    protected function EnginPlayCard( WebsocketClientInterface $client ): void
    {
        
    }
    
    protected function GetWinner(): ?CardGameTeam
    {
        
    }
    
    protected function FirstToPlay(): PlayerPosition
    {
        return $this->Game->firstInRound;
    }
}
