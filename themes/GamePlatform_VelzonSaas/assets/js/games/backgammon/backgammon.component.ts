import { Component, OnInit, Inject } from '@angular/core';
import { Observable } from 'rxjs';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { loginBySignatureSuccess } from '../application/+store/login.actions';
import { selectGameRoomSuccess } from '../application/+store/game.actions';

import { IAuth } from '../application/interfaces/auth';
import { Busy } from '../application/state/busy';
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';

import { AuthService } from '../application/services/auth.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { AppState } from '../application/state/app-state';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import ErrorReportDto from '_@/GamePlatform/Model/BoardGame/errorReportDto';

import cssGameString from './backgammon.component.scss'
import templateString from './backgammon.component.html'

@Component({
    selector: 'app-backgammon',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BackgammonComponent extends GameBaseComponent implements OnInit
{
    //game: BeloteCardGame;
    
    title   = 'Backgammon';
    busy$   = AppState.Singleton.busy.observe();
    errors$: Observable<ErrorState>;
  
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        @Inject( Actions ) private actions$: Actions,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService
    ) {
        super( authService, gameService, store );
        
        this.errors$ = AppState.Singleton.errors.observe();
        this.authService.repair();
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
        
//         this.actions$.pipe( ofType( loginBySignatureSuccess ) ).subscribe( ( auth: IAuth ) => {
//             console.log( auth );
//         });
        
        this.actions$.pipe( ofType( selectGameRoomSuccess ) ).subscribe( () => {
            let  auth    = this.authService.getAuth();
            //console.log( auth );
            
            if ( auth ) {
                const userDto = {
                    id: String( auth.id ),
                    name: auth.username,
                    email: auth.email,
                    socialProviderId: String( auth.id ),
                    socialProvider: '',
                    photoUrl: '',
                    createdNew: false
                } as UserDto;
                
                Busy.show();
                this.authService.signIn( userDto, auth.apiToken );
            }
        });
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
