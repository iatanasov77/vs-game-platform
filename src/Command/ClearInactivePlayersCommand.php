<?php namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;

#[AsCommand(
    name: 'app:clear-inactive-players',
    description: 'Clear Inactive Player Connections.',
    hidden: false
)]
final class ClearInactivePlayersCommand extends Command
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $mercureConnectionsRepository;
    
    /** @var int */
    private $ttl;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $mercureConnectionsRepository,
        int $ttl
    ) {
        parent::__construct();
        
        $this->doctrine                     = $doctrine;
        $this->mercureConnectionsRepository = $mercureConnectionsRepository;
        $this->ttl                          = $ttl; // seconds
    }
    
    protected function configure(): void
    {
        $this
            ->setHelp( 'This command allows you to Clear Inactive Player Connections.' )
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $em             = $this->doctrine->getManager();
        $connections    = $this->mercureConnectionsRepository->findAll();
        
        foreach ( $connections as $con ) {
            $minActive  = ( new \DateTime() )->sub( \DateInterval::createFromDateString( $this->ttl . ' seconds' ) );
            
            if (
                $con->isActive() &&
                $con->getUser()->getLastActiveAt() &&
                $con->getUser()->getLastActiveAt() < $minActive
            ) {
                $con->setActive( false );
                $em->persist( $con );
                $em->flush();
            }
        }
        
        return Command::SUCCESS;
    }
}