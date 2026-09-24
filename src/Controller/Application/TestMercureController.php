<?php namespace App\Controller\Application;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;
use Symfony\Component\Mercure\Exception\RuntimeException as MercureRuntimeException;



use Symfony\Component\Mercure\Discovery;
use Symfony\Component\Mercure\Authorization;


use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\FactoryInterface;
use Vankosoft\ApplicationBundle\Component\Status;
use App\Component\MercureLogger;
use App\Component\Utils\Guid;
use App\Component\ServerSentEvent\MercurePublisher;
use App\Component\ServerSentEvent\Message\AddAiPlayerMessage;

class TestMercureController extends AbstractController
{
    /** @var RouterInterface */
    private $router;
    
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var MessageBusInterface */
    private $messageBus;
    
    /** @var MercureLogger */
    private $logger;
    
    /** @var MercureLogger */
    private $mercurePublisher;
    
    /** @var RepositoryInterface */
    private $gamesRepository;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var FactoryInterface */
    private $gamePlayFactory;
    
    /** @var FactoryInterface */
    private $tempPlayersFactory;
    
    public function __construct(
        RouterInterface $router,
        ManagerRegistry $doctrine,
        MessageBusInterface $messageBus,
        MercureLogger $logger,
        MercurePublisher $mercurePublisher,
        RepositoryInterface $gamesRepository,
        RepositoryInterface $playersRepository,
        FactoryInterface $gamePlayFactory,
        FactoryInterface $tempPlayersFactory,
    ) {
        $this->router               = $router;
        $this->doctrine             = $doctrine;
        $this->messageBus           = $messageBus;
        $this->logger               = $logger;
        $this->mercurePublisher     = $mercurePublisher;
        $this->gamesRepository      = $gamesRepository;
        $this->playersRepository    = $playersRepository;
        $this->gamePlayFactory      = $gamePlayFactory;
        $this->tempPlayersFactory   = $tempPlayersFactory;
    }
    
    public function sendToTestSubscribingTopic( Request $request ): JsonResponse
    {
        $this->logger->log( 'sendToTestSubscribingTopic()' );
        
        /*  
        $topicUrl = $this->router->generate( 'vs_api_test_mercure_send_to_test_subscribing_topic', [
            'id' => $videoFile->getVideo()->getId()
        ], RouterInterface::ABSOLUTE_URL );
        */
        $topicUrl = 'https://example.com/books/1';
        
        $update = new Update(
            $topicUrl,
            \json_encode( ['update' => 'New update received at ' . date( "h:i:sa" )] ),
            true // private
        );
        
        $this->mercurePublisher->publish( $request, $update );
        
        $responseData   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'Update published',
        ];
        
        return new JsonResponse( $responseData );
    }
    
    public function addBackgammonAiPlayer( string $gameSlug, Request $request ): Response
    {
        $this->logger->log( 'addBackgammonAiPlayer()' );
        
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
        
        $responseData   = [
            'status'    => Status::STATUS_OK,
            'message'   => 'AI is joining...',
        ];
        
        return new JsonResponse( $responseData );
    }
}
