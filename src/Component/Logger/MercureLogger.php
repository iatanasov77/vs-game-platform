<?php namespace App\Component\Logger;

use Vankosoft\ApplicationBundle\Component\MyLoggerInterface;
use Psr\Log\LoggerInterface;

class MercureLogger implements MyLoggerInterface
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
    
    public function log( string $logData, ?string $context = null ): void
    {
        if ( $this->environement == 'dev' ) {
            $this->logger->info( \sprintf( "[Mercure HUB] %s", $logData ) );
        }
    }
}
