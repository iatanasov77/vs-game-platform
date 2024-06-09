import { Component, OnInit, OnDestroy, Input } from '@angular/core';

import CardGamePlayer from '_@/GamePlatform/Game/CardGamePlayer';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';
import * as GameEvents from '_@/GamePlatform/Game/GameEvents';
import { BridgeBeloteProvider } from '../../../../application/services/providers/bridge-belote-provider';

import templateString from './player-announce.component.html'
import styleString from './player-announce.component.scss'

declare var $: any;

@Component({
    selector: 'player-announce',
    
    template: templateString || 'Template Not Loaded !!!',
    styles: [
        styleString || 'CSS Not Loaded !!!'
    ]
})
export class PlayerAnnounceComponent implements OnInit, OnDestroy
{
    @Input() player?: CardGamePlayer;
    
    providerBridgeBelote: any;
    
    announceIcon: any;
    position: any;
    className: any;
    rotateClassName: any;
    styles: any;
    
    constructor(
        //private providerBridgeBelote: BridgeBeloteProvider
    ) {
        // DI Not Worked
        this.providerBridgeBelote   = new BridgeBeloteProvider();
        
        this.announceIcon   = null;
        
        this.className   = 'playerAnnounce';
        this.styles      = {
            position: "relative",
            top: "100px"
        };
    }
    
    ngOnInit(): void
    {
        this.position    = this.player?.id;
        switch ( this.player?.id ) {
            case 'left':
                this.className          += ' float-end';
                this.rotateClassName    = 'rotate-90-left';
                
                break;
            case 'top':
                this.className          += ' text-center align-middle';
                this.rotateClassName    = 'rotate-none';
                
                break;
            case 'right':
                this.className          += ' float-start';
                this.rotateClassName    = 'rotate-90-right';
                
                break;
            case 'bottom':
                this.className          += ' text-center align-middle';
                this.styles.top         = 0;
                this.rotateClassName    = 'rotate-bottom';
                
                break;
        }
        
        this.listenForGameEvents();
    }
    
    ngOnDestroy()
    {

    }
    
    listenForGameEvents()
    {
        $( "#" + this.player?.containerId ).get( 0 ).addEventListener( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, ( event:any ) => {
            const { announceId }    = event.detail;
            
            this.providerBridgeBelote.setAnnounce( this.player?.id, announceId );
            if ( this.position === this.player?.id ) {
                //alert( announceId );
                this.announceIcon   = this.providerBridgeBelote.getAnnounceSymbol( announceId )?.value;
            }
        });
    }
}
