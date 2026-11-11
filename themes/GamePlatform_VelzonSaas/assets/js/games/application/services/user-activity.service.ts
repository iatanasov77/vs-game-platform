import { Injectable, OnDestroy } from '@angular/core';
import { fromEvent, merge, timer, Subscription, Observable } from 'rxjs';
import { startWith, switchMap } from 'rxjs/operators';

/**
 * Manual: https://medium.com/@rajasaikiranvemula/%EF%B8%8F-%EF%B8%8F-detecting-user-inactivity-in-angular-using-rxjs-and-zone-js-4eda5cc79ea8
 */
@Injectable({
    providedIn: 'root'
})
export class UserActivityService
{
    private idleSub: Subscription | null = null;
    private readonly defaultIdleTime = 60_000; // 1 minute
    
    // Create stream of user activity events
    private get activityEvents$(): Observable<Event>
    {
        return merge(
            fromEvent( document, 'mousemove' ),
            fromEvent( document, 'keydown' ),
            fromEvent( document, 'scroll' ),
            fromEvent( document, 'click' )
        );
    }
    
    // Start monitoring for inactivity
    startMonitoringIdleTime( onIdle: () => void, idleTimeMs: number = this.defaultIdleTime ): void
    {
        this.stopMonitoring(); // clean up if already monitoring
        
        this.idleSub = this.activityEvents$.pipe(
            startWith( null ), // triggers initial timer immediately
            switchMap( () => timer( idleTimeMs ) ) // reset timer on any activity
        ).subscribe( onIdle );
    }
    
    // Stop monitoring
    stopMonitoring(): void
    {
        alert( '⚠️ Stop User Activity Monitoring' );
        
        this.idleSub?.unsubscribe();
        this.idleSub = null;
    }
    
    ngOnDestroy(): void
    {
        this.stopMonitoring();
    }
}