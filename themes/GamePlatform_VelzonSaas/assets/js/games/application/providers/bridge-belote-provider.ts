import { Injectable } from '@angular/core';

const { context } = require( '../context' );
import GameSettings from '_@/GamePlatform/Game/GameSettings';
import BeloteCardGame from '_@/GamePlatform/Game/BeloteCardGame';
import ICardGameProvider from '../interfaces/card-game-provider';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';
import CardGamePlayerModel from '_@/GamePlatform/Model/CardGamePlayerModel';
import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';

declare global {
    interface Window {
        gamePlatformSettings: any;
    }
}

@Injectable({
    providedIn: 'root'
})
export class BridgeBeloteProvider implements ICardGameProvider
{
    game?: BeloteCardGame;
    
    Players: Array<CardGamePlayerModel>  = [
        {
            id: 'left',
            announce: null
        },
        {
            id: 'top',
            announce: null
        },
        {
            id: 'right',
            announce: null
        },
        {
            id: 'bottom',
            announce: null
        }
    ];
    
    AnnounceSymbols: Array<CardGameAnnounceSymbolModel>  = [
        {
            id: Announce.CLOVER,
            key: "btnClover",
            tooltip: "Clover",
            
            value: '<i class="fi fi-sr-club"></i>'
            //value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/clover.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.DIAMOND,
            key: "btnDiamond",
            tooltip: "Diamond",
            
            value: '<i class="fi fi-sr-card-diamond"></i>'
            //value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/diamond.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.HEART,
            key: "btnHeart",
            tooltip: "Heart",
            
            value: '<i class="fi fi-sr-heart"></i>'
            //value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/hearts.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.SPADE,
            key: "btnSpade",
            tooltip: "Spade",
            
            value: '<i class="fi fi-sr-spade"></i>'
            //value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/symbol-of-spades.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.BEZ_KOZ,
            key: "btnBezKoz",
            tooltip: "Bez Koz",
            value: '<span class="announce-button">a</span>'
        },
        {
            id: Announce.VSICHKO_KOZ,
            key: "btnVsichkoKoz",
            tooltip: "Vsichko Koz",
            value: '<span class="announce-button">j</span>'
        },
        {
            id: Announce.KONTRA,
            key: "btnKontra",
            tooltip: "Kontra",
            value: '<span class="announce-button">kr</span>'
        },
        {
            id: Announce.RE_KONTRA,
            key: "btnReKontra",
            tooltip: "Re-Kontra",
            value: '<span class="announce-button">re-kr</span>'
        },
        {
            id: Announce.PASS,
            key: "btnPass",
            tooltip: "Pass",
            value: '<span class="announce-button">pass</span>'
        }
    ];
    
    gameSettings(): GameSettings
    {
        let gameSettings: GameSettings = {
            id: 'bridge-belote',
            publicRootPath: context.themeBuildPath,
            boardSelector: '#card-table',
            timeoutBetweenPlayers: window.gamePlatformSettings.timeoutBetweenPlayers,
        };
        
        return gameSettings;
    }
    
    getGame()
    {
        if ( ! this.game ) {
            this.game   = new BeloteCardGame( this.gameSettings() );
        }
        
        return this.game;
    }
    
    getPlayers(): Array<CardGamePlayerModel>
    {
        return this.Players;
    }
    
    getAnnounceSymbols(): Array<CardGameAnnounceSymbolModel>
    {
        return this.AnnounceSymbols;
    }
    
    getAnnounceSymbol( symboId: String )
    {
        return this.AnnounceSymbols.find( ( x: any ) => x.id === symboId );
    }
    
    getAnnounce( playerId: String )
    {
        return this.Players.find( ( x: any ) => x.id === playerId )?.announce;
    }
    
    setAnnounce( playerId: String, announceId: any )
    {
        let player  = this.Players.find( ( x: any ) => x.id === playerId );
        if ( player ) {
            player.announce = announceId;
        }
    }
}
