import { Injectable } from '@angular/core';
import { StateObject } from './state-object';

@Injectable({
    providedIn: 'root'
})
export class QueryParamsService
{
    variant: StateObject<string>;
    inviteId: StateObject<string>;
    
    gameId: StateObject<string>;
    playAi: StateObject<boolean>;
    forGold: StateObject<boolean>;
    tutorial: StateObject<boolean>;
    editing: StateObject<boolean>;
    
    constructor()
    {
        this.variant = new StateObject<string>();
        this.inviteId = new StateObject<string>();
        
        this.gameId = new StateObject<string>();
        this.playAi = new StateObject<boolean>();
        this.forGold = new StateObject<boolean>();
        this.tutorial = new StateObject<boolean>();
        this.editing = new StateObject<boolean>();
    }
}
