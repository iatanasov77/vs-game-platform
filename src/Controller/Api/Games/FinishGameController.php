<?php namespace App\Controller\Api\Games;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\ServerSentEvent\MercurePublisher;

class FinishGameController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var MercurePublisher */
    private $mercurePublisher;
    
    /** @var RepositoryInterface */
    private $gamePlayRepository;
    
    public function __construct(
        ManagerRegistry $doctrine,
        MercurePublisher $mercurePublisher,
        RepositoryInterface $gamePlayRepository
    ) {
        $this->doctrine             = $doctrine;
        $this->mercurePublisher     = $mercurePublisher;
        $this->gamePlayRepository   = $gamePlayRepository;
    }
    
    public function finishGameAction( $gamePlayId, Request $request ): JsonResponse
    {
        $gamePlay   = $this->gamePlayRepository->find( $request->request->get( 'game_play' ) );
        $em         = $this->doctrine->getManager();
        
        $gamePlay->setActive( false );
        
        $em->persist( $gamePlay );
        $em->flush();
        
        // This Method Should Be Replaced with App\Component\ServerSentEvent\MercurePublisher
        // $this->publishGamePlay( $gamePlay );
        
        return new JsonResponse([
            'status'    => Status::STATUS_OK,
            'data'      => [
                'id'        => $gamePlay->getId(),
            ],
        ]);
    }
}