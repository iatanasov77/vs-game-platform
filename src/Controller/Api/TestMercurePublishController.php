<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use App\Component\Logger\MercureLogger;

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

class TestMercurePublishController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var HubInterface */
    private $hub;
    
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var MercureLogger */
    private $logger;
    
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
        HubInterface $hub,
        SecurityBridge $vsSecurityBridge,
        MercureLogger $logger,
        RepositoryInterface $gameRepository,
        FactoryInterface $gamePlayFactory,
        RepositoryInterface $gamePlayRepository,
        RepositoryInterface $playersRepository,
        FactoryInterface $tempPlayersFactory,
    ) {
        $this->doctrine             = $doctrine;
        $this->hub                  = $hub;
        $this->vsSecurityBridge     = $vsSecurityBridge;
        $this->logger               = $logger;
        $this->gameRepository       = $gameRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->playersRepository    = $playersRepository;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    public function sendTestMessageAction( Request $request ): JsonResponse
    {
        $postData = \json_decode( $request->getContent(), true );
        $publishData    = json_encode([
            'status'   => 'checked out',
        ]);
        
        $update = new Update(
            $postData['topic'],
            $publishData,
        );
        
        $this->hub->publish( $update );
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Published From Backend',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function createGameLobby( $gameType, Request $request ): JsonResponse
    {
        if ( $gameType == GamePlatform::GAME_TYPE_CARD_GAME ) {
            $loby = $this->_createCardGameLobby();
        } else {
            $loby = $this->_createBoardGameLobby();
        }
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Created',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function joinGameLobby( $roomId, Request $request ): JsonResponse
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->vsSecurityBridge->getUser()->getPlayer();
        
        $this->logger->log( "Join Game Lobby !!!" );
        $tempPlayer = $this->createTempPlayer( $room->getGame(), $player );
        $tempPlayer->setGame( $room );
        $room->addGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Joined',
        ];
        
        return new JsonResponse( $response );
    }
    
    public function exitGameLobby( $roomId, Request $request ): JsonResponse
    {
        $room   = $this->gamePlayRepository->find( $roomId );
        $player = $this->vsSecurityBridge->getUser()->getPlayer();
        
        $tempPlayer = $room->getTempPlayer( $player );
        $room->removeGamePlayer( $tempPlayer );
        
        $em = $this->doctrine->getManager();
        $em->persist( $room );
        $em->flush();
        
        $response   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Test Game Lobby Leaved',
        ];
        
        return new JsonResponse( $response );
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
