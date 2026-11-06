<?php namespace App\Component\Manager;

use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use App\Component\ServerSentEvent\MercurePublisher;
use App\Component\Logger\MercureLogger;
use App\Controller\Api\Games\GameRoomResponseTrait;

// DTO
use App\Component\Dto\Actions\MercureActionNames;
use App\Component\Dto\Actions\MercureActionDto;
use App\Component\Dto\Actions\TestMercureMessageActionDto;
use App\Component\Dto\Actions\JoinGameLobbyActionDto;
use App\Component\Dto\Actions\LeaveGameLobbyActionDto;

// Types
use App\Component\GamePlatform;
use App\Component\Utils\Guid;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Component\Type\GameState;

use App\Entity\Game;
use App\Entity\GamePlay;
use App\Entity\GamePlayer;
use App\Entity\TempPlayer;

class MercureManager
{
    use GameRoomResponseTrait;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var MercurePublisher */
    private $mercurePublisher;
    
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var MercureLogger */
    private $logger;
    
    /** @var SerializerInterface */
    private $serializer;
    
    /** @var RepositoryInterface */
    private $gameRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        MercurePublisher $mercurePublisher,
        SecurityBridge $vsSecurityBridge,
        MercureLogger $logger,
        SerializerInterface $serializer,
        RepositoryInterface $gameRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $gamePlayRepository,
        RepositoryInterface $playersRepository,
        FactoryInterface $tempPlayersFactory,
    ) {
        $this->doctrine             = $doctrine;
        $this->mercurePublisher     = $mercurePublisher;
        $this->vsSecurityBridge     = $vsSecurityBridge;
        $this->logger               = $logger;
        $this->serializer           = $serializer;
        $this->gameRepository       = $gameRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->playersRepository    = $playersRepository;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    public function DoAction( string $topic, string $actionText ): string
    {
        $action     = $this->serializer->deserialize( $actionText, MercureActionDto::class, JsonEncoder::FORMAT );
        $actionName = MercureActionNames::from( $action->actionName );
        $this->logger->log( "Doing action: {$actionName->value}", 'MercureManager' );
        //$this->logger->debug( $this->Game->Points, 'BeforeDoAction.txt' );
        
        if ( $actionName == MercureActionNames::testMessage ) {
            $action = $this->serializer->deserialize( $actionText, TestMercureMessageActionDto::class, JsonEncoder::FORMAT );
            
            return 'Response: Published From Backend';
        } else if ( $actionName == MercureActionNames::createGameLobby ) {
            $gameType   = $action->gameType;
            if ( $gameType == GamePlatform::GAME_TYPE_CARD_GAME ) {
                $loby = $this->_createCardGameLobby();
            } else {
                $loby = $this->_createBoardGameLobby();
            }
            
            $gamePlay = $this->createResponseBody( $loby );
            $this->mercurePublisher->publishData( $topic, $gamePlay );
            
            return $json = $this->serializer->serialize( $gamePlay, JsonEncoder::FORMAT );
        } else if ( $actionName == MercureActionNames::joinGameLobby ) {
            $action = $this->serializer->deserialize( $actionText, JoinGameLobbyActionDto::class, JsonEncoder::FORMAT );
            
            $loby   = $this->gamePlayRepository->find( $action->room->id );
            $player = $this->vsSecurityBridge->getUser()->getPlayer();
            $this->_joinGameLobby( $loby, $player );
            
            $gamePlay = $this->createResponseBody( $loby );
            $this->mercurePublisher->publishData( $topic, $gamePlay );
            
            return $json = $this->serializer->serialize( $gamePlay, JsonEncoder::FORMAT );
        } else if ( $actionName == MercureActionNames::leaveGameLobby ) {
            $action = $this->serializer->deserialize( $actionText, LeaveGameLobbyActionDto::class, JsonEncoder::FORMAT );
            
            $loby   = $this->gamePlayRepository->find( $action->room->id );
            $player = $this->vsSecurityBridge->getUser()->getPlayer();
            $this->_leaveGameLobby( $loby, $player );
            
            $gamePlay = $this->createResponseBody( $loby );
            $this->mercurePublisher->publishData( $topic, $gamePlay );
            
            return $json = $this->serializer->serialize( $gamePlay, JsonEncoder::FORMAT );
        }
    }
    
    private function _joinGameLobby( $room, $player ): GamePlay
    {
        $this->logger->log( "Join Game Lobby !!!" );
        
        $tempPlayer = $this->createTempPlayer( $room->getGame(), $player );
        $tempPlayer->setGame( $room );
        $room->addGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        return $room;
    }
    
    private function _leaveGameLobby( $room, $player ): GamePlay
    {
        $this->logger->log( "Leave Game Lobby !!!" );
        
        $tempPlayer = $room->getTempPlayer( $player );
        $room->removeGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        return $room;
    }
    
    private function _createBoardGameLobby(): GamePlay
    {
        $aiPlayer = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
        
        $currentUser = $this->vsSecurityBridge->getUser();
        $blackPlayer = $this->playersRepository->find( $currentUser->getPlayer()->getId() );
        $whitePlayer = $this->playersRepository->find( $aiPlayer->getId() );
        
        $tempBlackPlayer = $this->CreateBoardGameTempPlayer( $blackPlayer, PlayerColor::Black );
        $tempWhitePlayer = $this->CreateBoardGameTempPlayer( $whitePlayer, PlayerColor::White );
        
        $gameBase   = $this->gameRepository->findOneBy(['slug' => 'debug-board-game-sse']);
        $game       = $this->gamePlayFactory->createNew();
        
        $game->setGame( $gameBase );
        $game->setOwner( $blackPlayer );
        $game->setGuid( Guid::NewGuid() );
        
        $tempBlackPlayer->setGame( $game );
        $tempWhitePlayer->setGame( $game );
        
        $game->addGamePlayer( $tempBlackPlayer );
        $game->addGamePlayer( $tempWhitePlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
        
        return $game;
    }
    
    private function _createCardGameLobby(): GamePlay
    {
        $aiPlayer = $this->playersRepository->findOneBy( ['guid' => GamePlayer::AiUser] );
        
        $currentUser = $this->vsSecurityBridge->getUser();
        $southPlayer = $this->playersRepository->find( $currentUser->getPlayer()->getId() );
        $eastPlayer = $this->playersRepository->find( $aiPlayer->getId() );
        $northPlayer = $this->playersRepository->find( $aiPlayer->getId() );
        $westPlayer = $this->playersRepository->find( $aiPlayer->getId() );
        
        $tempSouthPlayer = $this->CreateCardGameTempPlayer( $southPlayer, PlayerPosition::South );
        $tempEastPlayer = $this->CreateCardGameTempPlayer( $eastPlayer, PlayerPosition::East );
        $tempNorthPlayer = $this->CreateCardGameTempPlayer( $northPlayer, PlayerPosition::North );
        $tempWestPlayer = $this->CreateCardGameTempPlayer( $westPlayer, PlayerPosition::West );
        
        $gameBase   = $this->gameRepository->findOneBy(['slug' => 'debug-card-game-sse']);
        $game       = $this->gamePlayFactory->createNew();
        
        $game->setGame( $gameBase );
        $game->setOwner( $southPlayer );
        $game->setGuid( Guid::NewGuid() );
        
        $tempSouthPlayer->setGame( $game );
        $tempEastPlayer->setGame( $game );
        $tempNorthPlayer->setGame( $game );
        $tempWestPlayer->setGame( $game );
        
        $game->addGamePlayer( $tempSouthPlayer );
        $game->addGamePlayer( $tempEastPlayer );
        $game->addGamePlayer( $tempNorthPlayer );
        $game->addGamePlayer( $tempWestPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $game );
        $em->flush();
        
        return $game;
    }
    
    private function createTempPlayer( Game $baseGame, $player, $position = null ): TempPlayer
    {
        switch ( $baseGame->getType() ) {
            case GamePlatform::GAME_TYPE_BOARD_GAME:
                if ( ! $position ) {
                    $position = PlayerColor::Black;
                }
                
                return $this->CreateBoardGameTempPlayer( $player, $position );
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME:
            case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                if ( ! $position ) {
                    $position = PlayerPosition::South;
                }
                
                return $this->CreateBoardGameTempPlayer( $player, $position );
                
                break;
        }
    }
    
    private function CreateBoardGameTempPlayer( GamePlayer $player, PlayerColor $playerPosition ): TempPlayer
    {
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setColor( $playerPosition->toString() );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
    
    private function CreateCardGameTempPlayer( GamePlayer $player, PlayerPosition $playerPosition ): TempPlayer
    {
        $tempPlayer = $this->tempPlayersFactory->createNew();
        $tempPlayer->setGuid( Guid::NewGuid() );
        $tempPlayer->setPlayer( $player );
        $tempPlayer->setPosition( $playerPosition->toString() );
        $tempPlayer->setName( $player->getName() );
        $player->addGamePlayer( $tempPlayer );
        
        return $tempPlayer;
    }
}
