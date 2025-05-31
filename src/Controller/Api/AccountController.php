<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Entity\GamePlay;

class AccountController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var RepositoryInterface */
    private $usersRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var HubInterface */
    private $hub;
    
    /** @var CacheManager */
    private $imagineCacheManager;
    
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityBridge $vsSecurityBridge,
        RepositoryInterface $usersRepository,
        FactoryInterface $gamePlayFactory,
        HubInterface $hub,
        CacheManager $imagineCacheManager
    ) {
        $this->doctrine             = $doctrine;
        $this->vsSecurityBridge     = $vsSecurityBridge;
        $this->usersRepository      = $usersRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->hub                  = $hub;
        $this->imagineCacheManager  = $imagineCacheManager;
    }
    
    public function signinAction( Request $request ): JsonResponse
    {
        $user       = $this->vsSecurityBridge->getUser();
        $player     = $user->getPlayer();
        
        if ( $player->getPhotoUrl() ) {
            $photoPath  = $player->getPhotoUrl();
        } else {
            $photoPath  = $this->imagineCacheManager->resolve( $user->getInfo()->getAvatar()->getPath(), 'users_crud_index_thumb' );
        }
        
        $userDto    = \json_decode( $request->getContent() );
        
        $userDto->photoUrl          = $photoPath;
        $userDto->showPhoto         = $player->getShowPhoto();
        
        $userDto->preferredLanguage = "en";
        $userDto->theme             = "dark";
        $userDto->emailNotification = true;
        $userDto->gold              = $player->getGold();
        $userDto->Elo               = $player->getElo();
        
        return new JsonResponse( $userDto );
    }
    
    public function toggleIntroAction( Request $request ): JsonResponse
    {
        $mute   = false;
        $user   = $this->vsSecurityBridge->getUser();
        
        $player = $user->getPlayer();
        if ( $player ) {
            $mute   = $player->getMuteIntro();
            $player->setMuteIntro( ! $mute );
            $this->doctrine->getManager()->persist( $player );
            $this->doctrine->getManager()->flush();
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'mute'     => ! $mute,
        ]);
    }
}