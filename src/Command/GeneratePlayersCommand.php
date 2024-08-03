<?php namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Style\SymfonyStyle;

use Doctrine\Persistence\ManagerRegistry;
use Sylius\Component\Resource\Repository\RepositoryInterface;
use Sylius\Component\Resource\Factory\Factory;
use App\Entity\GamePlayer;

#[AsCommand(
    name: 'app:generate-players',
    description: 'Generate Game Players Command.',
    hidden: false
)]
class GeneratePlayersCommand extends Command
{
    /** @var ManagerRegistry */
    private $doctrine;
    
    /** @var RepositoryInterface */
    private $playersRepository;
    
    /** @var Factory */
    private $playersFactory;
    
    public function __construct(
        ManagerRegistry $doctrine,
        RepositoryInterface $playersRepository,
        Factory $playersFactory
    ) {
        parent::__construct();
        
        $this->doctrine             = $doctrine;
        $this->playersRepository    = $playersRepository;
        $this->playersFactory       = $playersFactory;
    }
    
    protected function configure(): void
    {
        $this
            ->setHelp( 'This command generates number of players.' )
            ->addOption( 'count', null, InputOption::VALUE_REQUIRED, 'Count Number of Players to Generate.' )
        ;
    }
    
    protected function execute( InputInterface $input, OutputInterface $output ): int
    {
        $countOption    = $input->getOption( 'count' );
        $em             = $this->doctrine->getManager();
        
        for ( $i = 0; $i < $countOption; $i++ ) {
            $player = $this->playersFactory->createNew();
            
            $player->setType( GamePlayer::TYPE_COMPUTER );
            $player->setName( 'player-' . ( $i + 1 ) );
            
            $em->persist( $player );
        }
        $em->flush();
        
        $style  = new SymfonyStyle( $input, $output );
        $style->success( 'Generating Players Finished Successfull !' );
        $style->newLine();
        
        return Command::SUCCESS;
    }
}