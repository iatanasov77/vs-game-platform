<?php namespace App\Controller\GamePlatform;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;

use App\Component\Utils\Guid;
use App\Component\ServerSentEvent\Message\AddAiPlayerMessage;

class DebugController extends AbstractController
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var MessageBusInterface */
    private $messageBus;
    
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        MessageBusInterface $messageBus,
        RepositoryInterface $gamesRepository,
        RepositoryInterface $playersRepository,
        FactoryInterface $gamePlayFactory,
        FactoryInterface $tempPlayersFactory
    ) {
        $this->doctrine             = $doctrine;
        $this->messageBus           = $messageBus;
        $this->gamesRepository      = $gamesRepository;
        $this->playersRepository    = $playersRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    public function index( Request $request ): Response
    {
        return $this->render( 'Pages/GamesDebug/index.html.twig', [
            
        ]);
    }
    
    public function addBackgammonAiPlayer( string $gameSlug, Request $request ): Response
    {
        $game       = $this->gamesRepository->findOneBy( ['slug' => $gameSlug] );
        $gameRoom   = $this->gamePlayFactory->createNew();
        // var_dump( $game ); die;
        
        $gameRoom->setGame( $game );
        $gameRoom->setGuid( Guid::NewGuid() );
        
        $em = $this->doctrine->getManager();
        $em->persist( $gameRoom );
        $em->flush();
        
        // 1. Dispatch background message to prevent blocking the HTTP response
        $this->messageBus->dispatch( new AddAiPlayerMessage( $gameRoom->getId() ) );
        
        return $this->json( ['status' => 'AI is joining...'] );
    }
}
