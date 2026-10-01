<?php namespace App\Component;

use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use App\Entity\TempPlayer;

final class GameTeamPlayer
{
    /** @var LiipImagineCacheManager */
    private $imagineCacheManager;
    
    /* @var string */
    private $photoUrlImage;
    
    public function __construct(
        string $joinUrl,
        ?LiipImagineCacheManager $imagineCacheManager = null
    ) {
        $this->imagineCacheManager  = $imagineCacheManager;
        $this->photoUrlImage        = "<i class=\"fa fa-sign-in fa-3x btnJoinRoomInPosition\" data-url=\"{$joinUrl}\"></i>";
    }
    
    public function setPlayer( TempPlayer $player ): void
    {
        if ( $player->getPhotoUrl() ) {
            $this->photoUrlImage = "<img class=\"tip\" height=\"46\" src=\"{$player->getPhotoUrl()}\">";
        }
        
        if ( $player->getAvatarPath() ) {
            $url = $this->imagineCacheManager->getBrowserPath(
                $player->getAvatarPath(),
                'users_crud_index_thumb',
            );
            
            $this->photoUrlImage = "<img class=\"tip\" height=\"46\" src=\"{$url}\">";
        } else {
            $photoUrl = "/build/gameplatform-velzonsaas-theme/images/game_player/locallogin.jpg";
            $this->photoUrlImage = "<img class=\"tip\" height=\"46\" src=\"{$photoUrl}\">"; // Player Has NOT Avatar
        }
    }
    
    public function getPhotoUrlImage(): string
    {
        return $this->photoUrlImage;
    }
}