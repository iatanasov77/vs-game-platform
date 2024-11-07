<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\GameService;
use App\Entity\GamePlay;

class SelectGameRoomController extends AbstractController
{
    /** @var GameService */
    private $gamesService;
    
    public function __construct(
        GameService $gamesService
    ) {
        $this->gamesService         = $gamesService;
    }
    
    /**
     * @NOTE This NOT Work Here Because Game Service is Different Instance in API Application From GamePlatform Application
     * 
     * @param string $gameId
     * @param Request $request
     * 
     * @return JsonResponse
     */
    public function selectGameRoomAction( $gameId, Request $request ): JsonResponse
    {
        $manager    = $this->gamesService->setGameRoomSelected( $gameId );
        if ( $manager ) {
            $manager->StartGame();
            
            return new JsonResponse([
                'status'    => Status::STATUS_OK,
                'data'      => [
                    'id'        => $manager->Game->id,
                ],
            ]);
        }
        
        return new JsonResponse([
            'status'    => Status::STATUS_ERROR,
            'message'   => 'Game Manager Not Exists in GameService.',
        ]);
    }
}