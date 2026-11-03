<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\ServerSentEvent\MercurePublisher;

class StartGameController extends AbstractController
{
    use GameRoomResponseTrait;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var MercurePublisher */
    private $mercurePublisher;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        MercurePublisher $mercurePublisher,
        RepositoryInterface $gamePlayRepository,
        FactoryInterface $gamePlayFactory
    ) {
        $this->doctrine             = $doctrine;
        $this->mercurePublisher     = $mercurePublisher;
        $this->gamePlayRepository   = $gamePlayRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
    }
    
    public function startGameAction( $gamePlayId, Request $request ): JsonResponse
    {
        $gamePlay   = $this->gamePlayRepository->find( $gamePlayId );
        if ( ! $gamePlay ) {
            $gamePlay   = $this->gamePlayFactory->createNew();
            $em         = $this->doctrine->getManager();
            
            $em->persist( $gamePlay );
            $em->flush();
        }
        
        // This Method Should Be Replaced with App\Component\ServerSentEvent\MercurePublisher
        // $this->publishGamePlay( $gamePlay );
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => $this->createResponseBody( $gamePlay ),
        ]);
    }
}