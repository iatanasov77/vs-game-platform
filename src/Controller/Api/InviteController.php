<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Vankosoft\UsersBundle\Security\SecurityBridge;
use App\Component\GameService;
use App\Component\Dto\rest\InviteResponseDto;

class InviteController extends AbstractController
{
    /** @var SecurityBridge */
    private $vsSecurityBridge;
    
    /** @var GameService */
    private $gamesService;
    
    public function __construct(
        SecurityBridge $vsSecurityBridge,
        GameService $gamesService
    ) {
        $this->vsSecurityBridge = $vsSecurityBridge;
        $this->gamesService     = $gamesService;
    }
    
    public function createInviteAction( $gameCode, $gameVariant, Request $request ): JsonResponse
    {
        $id = $this->gamesService->CreateInvite(
            $this->vsSecurityBridge->getUser()->getPlayer()->getId(),
            $gameCode,
            $gameVariant
        );
        
        $dto            = new InviteResponseDto();
        $dto->gameId    = $id;
        
        return new JsonResponse( $dto );
    }
}
