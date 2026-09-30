<?php namespace App\Component;

use Liip\ImagineBundle\Imagine\Cache\CacheManager as LiipImagineCacheManager;
use App\Entity\TempPlayer;

final class GameTeamPlayer
{
    private $photoUrlImage;
    
    public function __construct( ?TempPlayer $player = null, ?LiipImagineCacheManager $imagineCacheManager = null )
    {
        if ( $player ) {
            if ( $player->getPhotoUrl() ) {
                $this->photoUrlImage = "<img class=\"tip\" height=\"46\" src=\"{$player->getPhotoUrl()}\">";
            }
            
            if ( $player->getAvatarPath() ) {
                $url = $imagineCacheManager->getBrowserPath(
                    $player->getAvatarPath(),
                    'users_crud_index_thumb',
                );
                
                $this->photoUrlImage = "<img class=\"tip\" height=\"46\" src=\"{$url}\">";
            }
        } else {
            $this->photoUrlImage = "<i class=\"fa fa-sign-in fa-3x btnJoinRoomInPosition\"></i>";
        }
    }
    
    public function getPhotoUrlImage(): string
    {
        return $this->photoUrlImage;
    }
}