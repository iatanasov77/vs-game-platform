<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
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
    
    public function __construct(
        ManagerRegistry $doctrine,
        SecurityBridge $vsSecurityBridge,
        RepositoryInterface $usersRepository,
        FactoryInterface $gamePlayFactory,
        HubInterface $hub
    ) {
        $this->doctrine         = $doctrine;
        $this->vsSecurityBridge = $vsSecurityBridge;
        $this->usersRepository  = $usersRepository;
        $this->gamePlayFactory  = $gamePlayFactory;
        $this->hub              = $hub;
    }
    
    public function signinAction( Request $request ): JsonResponse
    {
        $userDto    = \json_decode( $request->getContent() );
        
        $userDto->photoUrl          = '';
        $userDto->showPhoto         = true;
        
        $userDto->preferredLanguage = "en";
        $userDto->theme             = "dark";
        $userDto->emailNotification = true;
        $userDto->gold              = 150;
        $userDto->Elo               = 1200;
        
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