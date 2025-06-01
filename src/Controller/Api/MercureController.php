<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Entity\GamePlay;

class MercureController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct( ManagerRegistry $doctrine, HubInterface $hub )
    {
        $this->doctrine = $doctrine;
        $this->hub      = $hub;
    }
    
    public function index( Request $request ): JsonResponse
    {
        $message    = $request->request->get( 'game_message' );
        
        
    }
    
    private function publish( GamePlay $gamePlay ): void
    {
        if ( $gamePlay->getGame()->getSlug() == 'backgammon' ) {
            return;    
        }
        
        $publishData    = json_encode([
            'type'      => 'GamePlayRoomUpdate',
            'action'    => 'StartGame',
            'target'    => $gamePlay->getGameRoom(),
        ]);
        
        $update = new Update(
            '/game-play',
            $publishData,
            false,
            null,
            'GamePlayRoomUpdate'
        );
        
        $this->hub->publish( $update );
    }
}
