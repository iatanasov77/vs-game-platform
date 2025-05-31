import { Component, OnInit, Inject } from '@angular/core';
import { Observable } from 'rxjs';
import { switchMap } from 'rxjs/operators';
import { Store } from '@ngrx/store';
import { Actions, ofType } from '@ngrx/effects';
import { loginBySignatureSuccess } from '../application/+store/login.actions';
import { playGame, selectGameRoomSuccess } from '../application/+store/game.actions';

import { IAuth } from '../application/interfaces/auth';
import { Busy } from '../application/state/busy';
import UserDto from '_@/GamePlatform/Model/BoardGame/userDto';

import { AuthService } from '../application/services/auth.service'
import { SoundService } from '../application/services/sound.service'
import { GameService } from '../application/services/game.service'
import { GameBaseComponent } from '../application/components/game-base/game-base.component';

import { AppStateService } from '../application/state/app-state.service';
import { ErrorState } from '../application/state/ErrorState';
import { ErrorReportService } from '../application/services/error-report.service';
import ErrorReportDto from '_@/GamePlatform/Model/BoardGame/errorReportDto';

import cssGameString from './backgammon-gulbara.component.scss'
import templateString from './backgammon-gulbara.component.html'

@Component({
    selector: 'app-backgammon-gulbara',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        cssGameString || 'Game CSS Not Loaded !!!',
    ]
})
export class BackgammonGulbaraComponent extends GameBaseComponent implements OnInit
{
    //game: BeloteCardGame;
    
    title   = 'Backgammon';
    busy$: Observable<Busy>;
    errors$: Observable<ErrorState>;
    
    lobbyButtonsVisible = true;
    playAi              = false;
    forGold             = false;
  
    constructor(
        @Inject( AuthService ) authService: AuthService,
        @Inject( SoundService ) soundService: SoundService,
        @Inject( GameService ) gameService: GameService,
        @Inject( Store ) store: Store,
        @Inject( Actions ) private actions$: Actions,
        
        @Inject( ErrorReportService ) private errorReportService: ErrorReportService,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        super( authService, soundService, gameService, store );
        
        this.errors$ = this.appState.errors.observe();
        this.busy$ = this.appState.busy.observe();
        this.authService.repair();
    }
    
    override ngOnInit()
    {
        super.ngOnInit();
        
//         this.actions$.pipe( ofType( loginBySignatureSuccess ) ).subscribe( ( auth: IAuth ) => {
//             console.log( auth );
//         });
        
        this.actions$.pipe( ofType( selectGameRoomSuccess ) ).subscribe( () => {
            // Not Needed Nothing
        });
        
        this.actions$.pipe( ofType( playGame ) ).subscribe( () => {
            this.lobbyButtonsVisible = false;
        });
    }
    
    saveErrorReport( errorDto: ErrorReportDto ): void
    {
        this.errorReportService.saveErrorReport( errorDto );
    }
}
