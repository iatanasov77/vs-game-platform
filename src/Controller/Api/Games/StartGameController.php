<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Entity\GamePlay;

class StartGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $roomsRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var HubInterface */
    private $hub;
    
    public function __construct(
        ManagerRegistry $doctrine,
        FactoryInterface $gamePlayFactory,
        HubInterface $hub
    ) {
        $this->doctrine         = $doctrine;
        $this->gamePlayFactory  = $gamePlayFactory;
        $this->hub              = $hub;
    }
    
    public function __invoke( Request $request ): JsonResponse
    {
        $gamePlay   = $this->gamePlayFactory->createNew();
        $em         = $this->doctrine->getManager();
        
        $gamePlay->setActive( true );
        
        $em->persist( $gamePlay );
        $em->flush();
        
        //$this->publishGamePlay( $gamePlay );
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => [
                'id'        => $gamePlay->getId(),
            ],
        ]);
    }
    
    private function publishGamePlay( GamePlay $gamePlay ): void
    {
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