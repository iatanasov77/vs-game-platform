import { Injectable, Inject } from '@angular/core';

import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http'
const { context } = require( '../context' );

import { Observable, map, of } from 'rxjs';
import { AuthService } from './auth.service';
import { AppConstants } from "../constants";
import { Keys } from '../utils/keys';

import GameCookieDto from '_@/GamePlatform/Model/BoardGame/gameCookieDto';
import IGamePlay from '_@/GamePlatform/Model/GamePlayInterface';
import ICardGameAnnounce from '_@/GamePlatform/CardGameAnnounce/CardGameAnnounceInterface';
import IGame from '_@/GamePlatform/Model/GameInterface';
import { InviteResponseDto } from '../dto/rest/inviteResponseDto';

import { QueryParamsService } from '../state/query-params.service';

@Injectable({
    providedIn: 'root'
})
export class GamePlayService
{
    apiUrl: string;
    backendUrl: string;
    
    constructor(
        @Inject( Router ) private router: Router,
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AuthService ) private authService: AuthService,
        @Inject( QueryParamsService ) private queryParamsService: QueryParamsService,
    ) {
        this.apiUrl     = `${context.apiURL}`;
        this.backendUrl = `${context.backendURL}`;
    }
    
    selectGameRoom( inputProps: any ): Observable<IGame>
    {
        //console.log( inputProps );
        return of( inputProps.game ).pipe( map( ( game: IGame ) => ({
            ...game,
            room: inputProps.room
        })));
    }
    
    createGameRoom( game: IGame ): Observable<IGamePlay>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.apiUrl}/create-game-room`;
        
        return this.httpClient.post<IGamePlay>( url, {game: game.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    startCardGame( game: IGame ): Observable<IGamePlay>
    {
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.apiUrl}/start-game`;
        
        return this.httpClient.post<IGamePlay>( url, {game_room: game.room.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    playerAnnounce(): Observable<ICardGameAnnounce>
    {
        let gameId      = 'bridge-belote';
        let announceId  = 'pass';
        
        return new Observable;
    }
    
    finishCardGame( gamePlay: any ): Observable<IGamePlay>
    {
        if ( ! gamePlay ) {
            return new Observable;
        }
        
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url         = `${this.apiUrl}/finish-game`;
        
        return this.httpClient.post<IGamePlay>( url, {game_room: gamePlay.room.id}, {headers} ).pipe(
            map( ( response: any ) => this.mapGamePlay( response ) )
        );
    }
    
    startBoardGame( variant: string ): void
    {
        const currentUrlparams = new URLSearchParams( window.location.search );
        let urlVariant = currentUrlparams.get( 'variant' );
        if ( urlVariant == null ) {
            urlVariant = variant;
        }
        
        const playAi = false;
        const forGold = true;
        this.queryParamsService.gameId.clearValue();
        
        this.queryParamsService.variant.setValue( urlVariant );
        this.queryParamsService.playAi.setValue( playAi );
        this.queryParamsService.forGold.setValue( forGold );
        
        const urlTree = this.router.createUrlTree([], {
            queryParams: { variant: urlVariant, playAi: playAi, forGold: forGold, gameId: null },
            queryParamsHandling: "merge",
            preserveFragment: true
        });
        this.router.navigateByUrl( urlTree );
    }
    
    exitBoardGame(): void
    {
        const currentUrlparams = new URLSearchParams( window.location.search );
        let urlVariant = currentUrlparams.get( 'variant' );
        if ( urlVariant == null ) {
            return;
        }
        
        this.queryParamsService.variant.setValue( urlVariant );
        this.queryParamsService.gameId.clearValue();
        this.queryParamsService.playAi.clearValue();
        this.queryParamsService.forGold.clearValue();
        
        const urlTree = this.router.createUrlTree([], {
            queryParams: { variant: urlVariant, playAi: null, forGold: null, gameId: null },
            queryParamsHandling: "merge",
            preserveFragment: true
        });
        this.router.navigateByUrl( urlTree );
    }
    
    createInvite( game: string, variant: string ): Observable<InviteResponseDto>
    {
        /* NOT Work on Api , Beecause Game Service is Different Instance From Websocket Server
         * ====================================================================================
        const headers   = ( new HttpHeaders() ).set( "Authorization", "Bearer " + this.authService.getApiToken() );
        var url = `${this.apiUrl}/invite/create/${game}-${variant}`;
        
        return this.httpClient.get<InviteResponseDto>( url, {headers} ).pipe(
            map( ( dto ) => dto as InviteResponseDto )
        );
        */
        
        var url = `${this.backendUrl}/ajax/invite/create/${game}-${variant}`;
        return this.httpClient.get<InviteResponseDto>( url ).pipe(
            map( ( dto ) => dto as InviteResponseDto )
        );
    }
    
    private mapGamePlay( response: any ): any
    {
        console.log( 'GamePlay Response: ', response );
        if ( response.status == AppConstants.RESPONSE_STATUS_OK && response.data ) {
            let gamePlay: IGamePlay = {
                id: response.data.id,
                room: response.data.room,
            };
            
            return gamePlay;
        }
        
        return response.message;
    }
}