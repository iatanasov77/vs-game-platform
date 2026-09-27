<?php namespace App\Component;

use Psr\Log\LoggerInterface;

class MercureLogger
{
    /** @var LoggerInterface */
    protected  $logger;
    
    /** @var string */
    protected $environement;
    
    public function __construct( LoggerInterface $logger, string $environement )
    {
        $this->logger       = $logger;
        $this->environement = $environement;
    }
    
    public function log( string $logData ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( \sprintf( "[Mercure HUB] %s", $logData ) );
        }
    }
}
