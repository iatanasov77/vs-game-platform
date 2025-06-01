<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Entity\GamePlay;

class FinishGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $gamePlayRepository,
        HubInterface $hub
    ) {
        $this->doctrine             = $doctrine;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->hub                  = $hub;
    }
    
    public function __invoke( Request $request ): JsonResponse
    {
        $gamePlay   = $this->gamePlayRepository->find( $request->request->get( 'game_play' ) );
        $em         = $this->doctrine->getManager();
        
        $gamePlay->setActive( false );
        
        $em->persist( $gamePlay );
        $em->flush();
        
        $this->publishGamePlay( $gamePlay );
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => [
                'id'        => $gamePlay->getId(),
            ],
        ]);
    }
    
    private function publishGamePlay( GamePlay $gamePlay ): void
    {
        if ( $gamePlay->getGame()->getSlug() == 'backgammon' ) {
            return;
        }
        
        $publishData    = json_encode([
            'type'      => 'GamePlayRoomUpdate',
            'action'    => 'FinishGame',
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