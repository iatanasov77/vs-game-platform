<?php namespace App\Component;

use Symfony\Component\Routing\RouterInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;
use App\Component\Type\CardGameTeam;

final class GameTeam
{
    /** @var RouterInterface */
    private $router;
    
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    public function __construct(
        RouterInterface $router,
        LiipImagineCacheManager $imagineCacheManager,
    ) {
        $this->router               = $router;
        $this->imagineCacheManager  = $imagineCacheManager;
    }
    
    public function createGameTeam( $room ): array
    {
        $team = $this->createEmptyGameTeam( $room );
        
        foreach ( $room->getGamePlayers() as $player ) {
            switch ( $room->getGame()->getType() ) {
                case GamePlatform::GAME_TYPE_BOARD_GAME:
                    $team[$player->getColor()][$player->getColor()]->setPlayer( $player );
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME:
                    if (
                        $player->getPosition() == PlayerPosition::North->toString() ||
                        $player->getPosition() == PlayerPosition::South->toString()
                    ) {
                        $team[CardGameTeam::SouthNorth->toString()][$player->getPosition()]->setPlayer( $player );
                    } else {
                        $team[CardGameTeam::EastWest->toString()][$player->getPosition()]->setPlayer( $player );
                    }
                    
                    break;
                case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                    $team[$player->getPosition()][$player->getPosition()]->setPlayer( $player );
                    
                    break;
                default:
                    throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
            }
        }
        
        return $team;   
    }
    
    private function createEmptyGameTeam( $room ): array
    {
        switch ( $room->getGame()->getType() ) {
            case GamePlatform::GAME_TYPE_BOARD_GAME:
                return $this->createEmptyBoardGameTeam( $room );
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME:
                return $this->createEmptyCardGameTeam( $room );
                
                break;
            case GamePlatform::GAME_TYPE_CARD_GAME_NO_TEAMS:
                $team = [
                    // new GameTeamPlayer(),
                ];
                
                break;
            default:
                throw new \RuntimeException( "Unknown Game Type for Game {$room->getGame()->getTitle()} !!!" );
        }
    }
    
    private function createEmptyBoardGameTeam( $room ): array
    {
        $joinBlackUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerColor::Black->toString()
        ]);
        
        $joinWhiteUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerColor::White->toString()
        ]);
        
        $team = [
            PlayerColor::Black->toString()  => [
                PlayerColor::Black->toString() => new GameTeamPlayer( $joinBlackUrl, $this->imagineCacheManager ),
            ],
            PlayerColor::White->toString()  => [
                PlayerColor::White->toString() => new GameTeamPlayer( $joinWhiteUrl, $this->imagineCacheManager ),
            ],
        ];
        
        return $team;
    }
    
    private function createEmptyCardGameTeam( $room ): array
    {
        $joinSouthUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerPosition::South->toString()
        ]);
        
        $joinNorthUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerPosition::North->toString()
        ]);
        
        $joinEastUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerPosition::East->toString()
        ]);
        
        $joinWestUrl = $this->router->generate( 'app_join_game_room_in_position', [
            'roomId'    => $room->getId(),
            'position'  => PlayerPosition::West->toString()
        ]);
        
        $team = [
            CardGameTeam::SouthNorth->toString()  => [
                PlayerPosition::South->toString() => new GameTeamPlayer( $joinSouthUrl, $this->imagineCacheManager ),
                PlayerPosition::North->toString() => new GameTeamPlayer( $joinNorthUrl, $this->imagineCacheManager ),
            ],
            CardGameTeam::EastWest->toString()  => [
                PlayerPosition::East->toString() => new GameTeamPlayer( $joinEastUrl, $this->imagineCacheManager ),
                PlayerPosition::West->toString() => new GameTeamPlayer( $joinWestUrl, $this->imagineCacheManager ),
            ],
        ];
        
        return $team;
    }
}