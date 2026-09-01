<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use React\Async;
use Ratchet\RFC6455\Messaging\Frame;

use App\Component\GameVariant;
use App\Component\Websocket\Client\WebsocketClientInterface;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\CardGame\Deck;
use App\Component\Rules\CardGame\Bid;
use App\Component\Dto\BidDto;
use App\Component\Rules\CardGame\Player;
use App\Component\Rules\CardGame\PlayCardAction;
use App\Component\Utils\Guid;
use App\Component\Utils\HumanName;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\BridgeBeloteGameMechanics\RoundResult;
use App\Component\Rules\CardGame\PlayerPositionExtensions;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

// Types
use App\Component\Type\CardGameTeam;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;
use App\Component\Type\AnnounceType;
use App\Component\Type\BidTrump;

// DTO Actions
use App\Component\Dto\Mapper;
use App\Component\Dto\Actions\ActionNames;
use App\Component\Dto\Actions\ConnectionInfoActionDto;
use App\Component\Dto\Actions\GameCreatedActionDto;
use App\Component\Dto\Actions\GameRestoreActionDto;
use App\Component\Dto\Actions\BiddingStartedActionDto;
use App\Component\Dto\Actions\BidMadeActionDto;
use App\Component\Dto\Actions\OpponentBidsActionDto;
use App\Component\Dto\Actions\AnnounceMadeActionDto;
use App\Component\Dto\Actions\PlayCardActionDto;
use App\Component\Dto\Actions\DummyPlayCardActionDto;
use App\Component\Dto\Actions\OpponentPlayCardActionDto;
use App\Component\Dto\Actions\PlayingStartedActionDto;
use App\Component\Dto\Actions\TrickEndedActionDto;
use App\Component\Dto\Actions\RoundEndedActionDto;
use App\Component\Dto\Actions\GameEndedActionDto;

abstract class CardGameManager extends AbstractGameManager
{
    public function Restore( int $playerPositionId, WebsocketClientInterface $socket ): void
    {
        $position = PlayerPosition::from( $playerPositionId );
        
        $gameDto = Mapper::CardGameToDto( $this->Game );
        $restoreAction = new GameRestoreActionDto();
        $restoreAction->game = $gameDto;
        $restoreAction->position = $position;
        
        $this->Clients->set( $position->value, $socket );
        $otherSockets = [];
        foreach ( $this->Clients->toArray() as $key => $client ) {
            if ( $key !== $position->value ) {
                $otherSockets[$key] = $client;
            }
        }
        
        $this->Send( $socket, $restoreAction );
        
        //Also send the state to the other clients in case it has made moves.
        foreach ( $otherSockets as $key => $otherSocket ) {
            if ( $otherSocket != null && $otherSocket->State == WebSocketState::Open ) {
                $restoreAction->position = PlayerPosition::from( $key );
                $this->Send( $otherSocket, $restoreAction );
            }
        }
    }
    
    public function StartGame(): void
    {
        $this->Game->ThinkStart = new \DateTime( 'now' );
        
        $gameDto = Mapper::CardGameToDto( $this->Game );
        // $this->logger->log( 'Begin Start Game: ' . \print_r( $gameDto, true ), 'GameManager' );
        
        $action = new GameCreatedActionDto();
        $action->game = $gameDto;
        
        $action->myPosition = PlayerPosition::South;
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        
        $action->myPosition = PlayerPosition::East;
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        
        $action->myPosition = PlayerPosition::North;
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        
        $action->myPosition = PlayerPosition::West;
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
        
        $this->startGameBidding();
    }
    
    public function EndRound(): void
    {
        $this->logger->log( "Card_Game_Round_Ended !!!", 'GameManager' );
        
        $score = $this->Game->GetNewScore();
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        $this->Game->PlayState = GameState::roundEnded;
        
        $this->Game->southNorthPoints += $score->SouthNorthPoints;
        $this->Game->eastWestPoints += $score->EastWestPoints;
        $this->Game->hangingPoints = $score->HangingPoints;
        
        $action = new RoundEndedActionDto();
        $action->game = Mapper::CardGameToDto( $this->Game );
        
        $newScore = Mapper::RoundResultToDto( $score );
        $newScore->contract = Mapper::BidToDto( $this->Game->CurrentContract );
        $action->newScore = $newScore;
        
        // Debug Tricks
        $action->SouthNorthTricks = $this->Game->SouthNorthTricks->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, $this->Game->GameCode );
            }
        )->toArray();
        
        $action->EastWestTricks = $this->Game->EastWestTricks->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, $this->Game->GameCode );
            }
        )->toArray();
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $action );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $action );
        
        $winner = $this->GetWinner();
        if ( $winner ) {
            $this->logger->log( "{$winner->value} won Game {$this->Game->Id}", 'GameManager' );
            $this->EndGame( $winner );
        }
    }
    
    public function StartNewRound(): void
    {
        $this->Game->roundNumber++;
        $this->Game->PlayState = GameState::firstBid;
        $this->Game->Deck = new Deck( $this->GameCode );
        
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        $this->Game->BidHistory = new ArrayCollection();
        $this->Game->SouthNorthTricks = new ArrayCollection();
        $this->Game->EastWestTricks = new ArrayCollection();
        
        $this->StartGame();
    }
    
    public function StartNewGame(): void
    {
        $this->Game->Deck = new Deck( $this->GameCode );
        $this->Game->Pile = new ArrayCollection();
        $this->Game->SouthNorthTricks = new ArrayCollection();
        $this->Game->EastWestTricks = new ArrayCollection();
        
        $this->Game->AvailableBids = new ArrayCollection();
        $this->Game->ValidCards = new ArrayCollection();
        $this->Game->Bids = new ArrayCollection();
        $this->Game->announces = new ArrayCollection();
        
        $this->Game->roundNumber = 1;
        $this->Game->trickNumber = 1;
        $this->Game->southNorthPoints = 0;
        $this->Game->eastWestPoints = 0;
        $this->Game->hangingPoints = 0;
        
        $this->Game->firstInRound = PlayerPosition::South;
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        $this->Game->PlayState = GameState::firstBid;
        
        $this->StartGame();
    }
    
    public function DoAction(
        ActionNames $actionName,
        string $actionText,
        WebsocketClientInterface $socket,
        //?WebsocketClientInterface $otherSocket
        array $otherSockets
    ): void {
        $this->logger->log( "Doing action: {$actionName->value}", 'GameManager' );
        
        if ( $actionName == ActionNames::bidMade ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, BidMadeActionDto::class, JsonEncoder::FORMAT );
            
            $this->DoBid( $action );
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        } else if ( $actionName == ActionNames::opponentBids ) {
            $action = $this->serializer->deserialize( $actionText, OpponentBidsActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::playCard ) {
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, PlayCardActionDto::class, JsonEncoder::FORMAT );
            
            $this->PlayCard( $action );
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        } else if ( $actionName == ActionNames::dummyPlayCard ) {
            if  ( ! $this->Game->DummyPlayer ) {
                $DummyPlayer = PlayerPositionExtensions::GetTeammate( $this->Game->CurrentContract->Player );
                
                $this->Game->DummyPlayer    = $DummyPlayer;
                $this->Game->DummyOwner     = $this->Game->CurrentContract->Player;
                $this->Game->DummyFaceup    = true;
            }
            
            $this->Game->ThinkStart = new \DateTime( 'now' );
            $action = $this->serializer->deserialize( $actionText, DummyPlayCardActionDto::class, JsonEncoder::FORMAT );
            
            $this->PlayCard( $action );
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        } else if ( $actionName == ActionNames::opponentPlayCard ) {
            $action = $this->serializer->deserialize( $actionText, OpponentPlayCardActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::startNewRound ) {
            $this->StartNewRound();
            $this->PlayRound( $socket );
        } else if ( $actionName == ActionNames::startNewGame ) {
            // New Game in the Same GameSession / GameRoom
            $this->StartNewGame();
            $this->PlayRound( $socket );
        } else if ( $actionName == ActionNames::connectionInfo ) {
            $action = $this->serializer->deserialize( $actionText, ConnectionInfoActionDto::class, JsonEncoder::FORMAT );
            foreach ( $otherSockets as $otherSocket ) {
                $this->Send( $otherSocket, $action );
            }
        } else if ( $actionName == ActionNames::resign ) {
//             $winner = $this->Clients->get( PlayerColor::Black->value ) == $otherSocket ? PlayerColor::Black : PlayerColor::White;
//             $this->Resign( $winner );
        } else if ( $actionName == ActionNames::exitGame ) {
            $this->Game->CurrentContract = null;
            $this->CloseConnections( $socket );
        }
    }
    
    protected function CreateDbGame(): void
    {
        $southPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::South->value]->Id, PlayerPosition::South->value );
        $eastPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::East->value]->Id, PlayerPosition::East->value );
        $northPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::North->value]->Id, PlayerPosition::North->value );
        $westPlayer = $this->CreateTempPlayer( $this->Game->Players[PlayerPosition::West->value]->Id, PlayerPosition::West->value );
        
        // Create Game Session
        $gameBase   = $this->gameRepository->findOneBy(['slug' => $this->GameCode]);
        $game       = $this->gamePlayFactory->createNew();
        $game->setGame( $gameBase );
        $game->setGuid( $this->Game->Id );
        
        $southPlayer->setGame( $game );
        $eastPlayer->setGame( $game );
        $northPlayer->setGame( $game );
        $westPlayer->setGame( $game );
        
        $game->addGamePlayer( $southPlayer );
        $game->addGamePlayer( $eastPlayer );
        $game->addGamePlayer( $northPlayer );
        $game->addGamePlayer( $westPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
    }
    
    protected function NewTurn( WebsocketClientInterface $socket ): void
    {
        /** $this->Game->DummyFaceup May be Uneeded */
        if ( $this->Game->PlayState == GameState::playing && $this->IsDummy() && ! $this->Game->DummyFaceup ) {
            $this->logger->log( "This is Dummy Player !!!", 'GameManager' );
            return;
        }
        
        $this->Game->SwitchPlayer();
        
        // Check/Set Trick Winner
        if ( ! $this->ContinuePlay() ) {
            return;
        }
        
        // Engine Bidding or Playing
        $this->PlayRound( $socket );
    }
    
    protected function AisTurn(): bool
    {
        switch ( $this->Game->CurrentPlayer ) {
            case PlayerPosition::South:
                $plyr = $this->Game->Players[PlayerPosition::South->value];
                break;
            case PlayerPosition::North:
                $plyr = $this->Game->Players[PlayerPosition::North->value];
                break;
            case PlayerPosition::West:
                $plyr = $this->Game->Players[PlayerPosition::West->value];
                break;
                break;
            case PlayerPosition::East:
                $plyr = $this->Game->Players[PlayerPosition::East->value];
                break;
            default:
                throw new \RuntimeException( 'Wrong Current Player !' );
        }
        
        $this->logger->log( "AisTurn CurrentPlayer: " . \print_r( $plyr, true ) , 'SwitchPlayer' );
        return $plyr->IsAi();
    }
    
    protected function PlayRound( WebsocketClientInterface $socket ): void
    {
        $PlayStateLog = "{$this->Game->PlayState->value} CurrentPlayer: {$this->Game->CurrentPlayer->value}";
        $this->logger->log( "Play Round -> PlayState: {$PlayStateLog}", 'GameManager' );
        
        if ( $this->Game->PlayState != GameState::roundEnded && $this->AisTurn() ) {
            $this->logger->log( "NewTurn for AI", 'SwitchPlayer' );
            if ( $this->Game->PlayState == GameState::bidding ) {
                $this->EnginBids( $socket );
            } else {
                $this->EnginPlayCard( $socket );
            }
            
            $promise = Async\async( function () use ( $socket ) {
                $this->NewTurn( $socket );
            })();
            Async\await( $promise );
        }
    }
    
    protected function startGameBidding(): void
    {
        $this->Game->PlayState = GameState::firstBid;
        while ( $this->Game->PlayState == GameState::firstBid ) {
            $this->logger->log( 'First Bid State !!!', 'FirstBidState' );
            
            $this->Game->SetFirstBidWinner();
            $biddingStartedAction = new BiddingStartedActionDto();
            
            $biddingStartedAction->deck = \array_values( $this->Game->Deck->Cards()->map(
                function( $entry ) {
                    return Mapper::CardToDto( $entry, $this->Game->GameCode );
                }
            )->toArray() );
            
            foreach ( $this->Game->Players as $key => $player ) {
                $biddingStartedAction->playerCards[$key] = $this->Game->playerCards[$key]->map(
                    function( $entry ) use ( $player ) {
                        return Mapper::CardToDto( $entry, $this->Game->GameCode, $player->PlayerPosition );
                    }
                )->toArray();
            }
            
            $biddingStartedAction->firstToBid = $this->Game->CurrentPlayer;
            $validBids = $this->Game->AvailableBids->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
            $biddingStartedAction->validBids = \array_values( $validBids );
            
            $biddingStartedAction->timer = Game::ClientCountDown;
            
            $this->Send( $this->Clients->get( PlayerPosition::South->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::East->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::North->value ), $biddingStartedAction );
            $this->Send( $this->Clients->get( PlayerPosition::West->value ), $biddingStartedAction );
        }
    }
    
    protected function StartGamePlay(): void
    {
        $this->Game->CurrentPlayer = $this->Game->firstInRound;
        
        $FirstToPlay            = $this->FirstToPlay();
        $playingStartedAction   = new PlayingStartedActionDto();
        
        $playingStartedAction->deck = \array_values( $this->Game->Deck->Cards()->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, $this->Game->GameCode );
            }
        )->toArray() );
        
        foreach ( $this->Game->Players as $key => $player ) {
            $playingStartedAction->playerCards[$key] = $this->Game->playerCards[$key]->map(
                function( $entry ) use ( $player ) {
                    return Mapper::CardToDto( $entry, $this->Game->GameCode, $player->PlayerPosition );
                }
            )->toArray();
            
            $playerAnnounces = $this->Game->GetAvailableAnnounces( $this->Game->playerCards[$key] );
            $this->logger->log( "Player Announces" . \print_r( $playerAnnounces->toArray(), true ), 'GameManager' );
            $playingStartedAction->playerAnnounces[$key] = $playerAnnounces->map(
                function( $entry ) use ( $player ) {
                    return Mapper::AnnounceToDto( $entry, $player->PlayerPosition );
                }
            )->toArray();
            
            foreach ( $playerAnnounces as $announce ) {
                $announce->Player = $player->PlayerPosition;
                $this->Game->announces[] = $announce;
            }
        }
        
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$this->Game->CurrentPlayer->value],
            $this->Game->CurrentContract,
            new ArrayCollection()
        );
        
        $playingStartedAction->firstToPlay = $FirstToPlay;
        $playingStartedAction->contract = Mapper::BidToDto( $this->Game->CurrentContract );
        $playingStartedAction->validCards = $this->Game->ValidCards->map(
            function( $entry ) {
                return Mapper::CardToDto( $entry, $this->Game->GameCode, $this->Game->CurrentPlayer );
            }
        )->toArray();
        $playingStartedAction->timer = Game::ClientCountDown;
        
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $playingStartedAction );
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $playingStartedAction );
        
        $this->Game->PlayState      = GameState::playing;
        $this->Game->CurrentPlayer  = $FirstToPlay;
        
        $this->logger->log( "Start Game Play !!!", 'GameManager' );
        $socket = $this->Clients->get( PlayerPosition::South->value );
        $this->PlayRound( $socket );
    }
    
    protected function EnginBids( WebsocketClientInterface $client ): void
    {
        $this->logger->log( "Manager -> EnginBids", 'GameManager' );
        
        // Debug Player Cards
        $playerCards = $this->Game->playerCards[$this->Game->CurrentPlayer->value];
        
        $engineBid  = $this->Engine->DoBid();
        $bidDto = $this->_createBidDto( $engineBid );
        $this->Game->BidHistory[] = $engineBid;
        
        $promise = Async\async( function () use ( $client, $engineBid, $bidDto, $playerCards ) {
            $sleepMileseconds   = \rand( 700, 1200 );
            Async\delay( $sleepMileseconds / 1000 );
            
            $nextPlayer = $this->Game->NextPlayer();
            $this->Game->SetContract( $engineBid, $nextPlayer );
            
            $action = new OpponentBidsActionDto();
            $action->bid = $bidDto;
            
            $validBids = $this->Game->AvailableBids->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
            $action->validBids = \array_values( $validBids );
            
            $bidHistory = $this->Game->BidHistory->map(
                function( $entry ) {
                    return Mapper::BidToDto( $entry );
                }
            )->toArray();
            $action->bidHistory = \array_values( $bidHistory );
            
            $action->nextPlayer = $nextPlayer;
            $action->playState = $this->Game->PlayState;
            
            $action->MyCards = $playerCards->map(
                function( $entry ) {
                    return Mapper::CardToDto( $entry, $this->Game->GameCode, $this->Game->CurrentPlayer );
                }
            );
            
            $this->Send( $client, $action );
        })();
        Async\await( $promise );
    }
    
    protected function OpponentPlayCardAction( PlayCardAction $playCardAction, WebsocketClientInterface $client ): void
    {
        $nextPlayer = $this->Game->NextPlayer();
        $this->Game->AddTrickAction( $playCardAction );
        
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$nextPlayer->value],
            $this->Game->CurrentContract,
            $this->Game->GetTrickActions()
        );
        
        $action = new OpponentPlayCardActionDto();
        $action->Card = Mapper::CardToDto( $playCardAction->Card, $this->Game->GameCode, $playCardAction->Player );
        $action->Belote = $playCardAction->Belote;
        $action->Player = $playCardAction->Player;
        $action->TrickNumber = $playCardAction->TrickNumber;
        
        $action->validCards = $this->Game->ValidCards->map(
            function( $entry ) use ( $nextPlayer ) {
                return Mapper::CardToDto( $entry, $this->Game->GameCode, $nextPlayer ); // PlayerPosition::South
            }
        )->getValues(); // ->toArray();
        $action->nextPlayer = $nextPlayer;
        
        $this->Send( $client, $action );
    }
    
    protected function SendTrickWinner( PlayerPosition $winner ): void
    {
        $this->Game->ValidCards = $this->Game->GetValidCards(
            $this->Game->playerCards[$winner->value],
            $this->Game->CurrentContract,
            new ArrayCollection()
        );
        $game = Mapper::CardGameToDto( $this->Game );
        
        $promise = Async\async( function () use ( $game ) {
            Async\delay( 1.2 );
            
            $trickEndedAction = new TrickEndedActionDto();
            $trickEndedAction->game = $game;
            
            $this->Send( $this->Clients->get( PlayerPosition::South->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::East->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::North->value ), $trickEndedAction );
            $this->Send( $this->Clients->get( PlayerPosition::West->value ), $trickEndedAction );
        })();
        Async\await( $promise );
    }
    
    protected function SaveWinner( CardGameTeam $team ): ?array
    {
        //return [$scoreBlack, $scoreWhite];
        return [null, null];
    }
    
    protected function SendWinner( CardGameTeam $team, ?RoundResult $newScore = null ): void
    {
        $game = Mapper::CardGameToDto( $this->Game );
        $game->winner = $team;
        $gameEndedAction = new GameEndedActionDto();
        $gameEndedAction->game = $game;
        
        //$gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerPosition::South->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerPosition::East->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[0] : null;
        $this->Send( $this->Clients->get( PlayerPosition::North->value ), $gameEndedAction );
        
        //$gameEndedAction->newScore = $newScore ? $newScore[1] : null;
        $this->Send( $this->Clients->get( PlayerPosition::West->value ), $gameEndedAction );
    }
    
    protected function Resign( PlayerPosition $winner ): void
    {
        $this->EndGame( $winner );
        $this->logger->log( "{$winner} won Game {$this->Game->Id} by resignition.", 'GameManager' );
    }
    
    protected function EndGame( CardGameTeam $winner ): void
    {
        //$this->moveTimeOut->cancel();
        $this->Game->PlayState = GameState::ended;
        $this->logger->log( "The winner is {$winner->value}", 'EndGame' );
        
        $newScore = $this->SaveWinner( $winner );
        $this->SendWinner( $winner );
    }
    
    protected function CloseConnections( WebsocketClientInterface $socket ): void
    {
        if ( $socket != null ) {
            $this->logger->log( "Closing client", 'ExitGame' );
            $socket->close( Frame::CLOSE_NORMAL );
            
            // Dispose Websocket
            if ( $socket == $this->Clients->get( PlayerPosition::South->value ) ) {
                $this->Clients->set( PlayerPosition::South->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::North->value ) ) {
                $this->Clients->set( PlayerPosition::North->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::East->value ) ) {
                $this->Clients->set( PlayerPosition::East->value, null );
            } else if ( $socket == $this->Clients->get( PlayerPosition::West->value ) ) {
                $this->Clients->set( PlayerPosition::West->value, null );
            }
        }
    }
    
    protected function _createBidDto( Bid $bid ): BidDto
    {
        $this->logger->log( "EnginBids -> Create BidDto !!!", 'GameManager' );
        
        $bidDto = Mapper::BidToDto( $bid );
        $ConsecutivePasses = $this->Game->ConsecutivePasses;
        
        if ( $bidDto->Trump == BidTrump::Pass->value() ) {
            $ConsecutivePasses++;
        }
        
        $LastBid = false;
        if (
            $this->Game->CurrentContract &&
            $this->Game->CurrentContract->Trump->get() == BidTrump::Pass->bitMaskValue() &&
            $ConsecutivePasses == 4
        ) {
            $LastBid = true;
        }
            
        if (
            $this->Game->CurrentContract &&
            $this->Game->CurrentContract->Trump->get() != BidTrump::Pass->bitMaskValue() &&
            $ConsecutivePasses == 3
        ) {
            $LastBid = true;
        }
        $bidDto->LastBid = $LastBid;
        
        $this->logger->log( "EnginBids -> Consecutive Passes: {$ConsecutivePasses}", 'GameManager' );
        $this->logger->log( "EnginBids -> Game Contract: {$this->Game->CurrentContract}", 'GameManager' );
        
        return $bidDto;
    }
    
    protected function InitializePlayer( GamePlayer $dbUser, bool $aiUser, Player &$player ): void
    {
        if ( $aiUser ) {
            $playerName = HumanName::generate();
        } else {
            $playerName = $dbUser != null ? $dbUser->getName() : "Guest";
        }
        
        $player->Id = $dbUser != null ? $dbUser->getId() : 0;
        $player->Guid = $dbUser != null ? $dbUser->getGuid() : Guid::Empty();
        $player->Name = $playerName;
        $player->Photo = $dbUser != null && $dbUser->getShowPhoto() ? $this->getPlayerPhotoUrl( $dbUser ) : "";
        $player->Elo = $dbUser != null ? $dbUser->getElo() : 0;
        
        if ( $this->Game->IsGoldGame ) {
            $player->Gold = $dbUser != null ? $dbUser->getGold() - self::firstBet : 0;
        }
    }
    
    abstract protected function DoBid( BidMadeActionDto $action ): void;
    
    abstract protected function PlayCard( PlayCardActionDto $action ): void;
    
    abstract protected function EnginPlayCard( WebsocketClientInterface $client ): void;
    
    abstract protected function GetWinner(): ?CardGameTeam;
    
    abstract protected function FirstToPlay(): PlayerPosition;
    
    private function CreateTempPlayer( int $playerId, int $playerPositionId ): TempPlayer
    {
        $player = $this->playersRepository->find( $playerId );
        
        if ( $this->Game->IsGoldGame && $player->getGold() < self::firstBet ) {
            throw new \RuntimeException( "Black player dont have enough gold" ); // Should be guarder earlier
        }
        
        if ( $this->Game->IsGoldGame && ! $this->IsAi( $player->getGuid() ) ) {
            $player->setGold( self::firstBet );
        }
        
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setPosition( $playerPositionId );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
}
