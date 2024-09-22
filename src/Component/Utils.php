<?php namespace App\Component;

use Amp\DeferredCancellation;

final class Utils
{
    public static function DoAfter( int $ms, Action action, DeferredCancellation $cancellation ): vod
    {
        await Task.Delay(ms);
        if ( ! $cancellation->isCancelled() ) {
            action.Invoke();
        }
        
        return;
    }

    public static function RepeatEvery( int $ms, Action action, DeferredCancellation $cancellation ): vod
    {
        while ( ! $cancellation->isCancelled() ) {
            await Task.Delay(ms);
            action.Invoke();
        }
    }
}
