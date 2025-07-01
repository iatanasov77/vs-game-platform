<?php namespace App\Component;

use Psr\Log\LoggerInterface;

class GameLogger
{
    /** @var LoggerInterface */
    protected  $logger;
    
    /** @var string */
    protected $environement;
    
    /** @var string */
    protected $projectDir;
    
    /** @var array */
    protected $logContexts;
    
    public function __construct( LoggerInterface $logger, string $environement, string $projectDir, array $logContexts )
    {
        $this->logger       = $logger;
        $this->environement = $environement;
        $this->projectDir   = $projectDir;
        $this->logContexts  = $logContexts;
    }
    
    public function log( string $logData, string $context ): void
    {
        if ( $this->environement == 'dev' && \in_array( $context, $this->logContexts ) ) {
            $this->logger->info( \sprintf( "[%s] %s", $context, $logData ) );
        }
    }
    
    public function debug( $logData, ?string $file = null ): void
    {
        if ( $this->environement == 'dev' ) {
            if ( ! $file ) {
                $file = 'debug.txt';
            }
            
            $now    = new \DateTime( 'now' );
            \file_put_contents(
                $this->projectDir . '/var/' . $file,
                $now->format( 'Y-m-d H:i:s' ) . "\n\n" . \print_r( $logData, true )
            );
        }
    }
}
