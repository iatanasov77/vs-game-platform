<?php namespace App\Component\Wamp;

use Voryx\ThruwayBundle\Annotation\Worker;
use Voryx\ThruwayBundle\Annotation\Subscribe;

/**
 * @Worker( "game-platform" )
 */
#[Worker( 'game-platform' )]
class WampCallback
{
    /**
     * @Subscribe( "game" )
     */
    #[Subscribe( 'game' )]
    public function onSubscribe( array $args ): void
    {
        echo "Event {$args[0]}\n";
    }
}
