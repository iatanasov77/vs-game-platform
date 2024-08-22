import { Injectable } from '@angular/core';

const { context } = require( '../context' );
import ICardGameProvider from '../interfaces/card-game-provider';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';

@Injectable({
    providedIn: 'root'
})
export class BridgeBeloteProvider implements ICardGameProvider
{
    Players: any    = [
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
    
    AnnounceSymbols: any   = [
        {
            id: Announce.CLOVER,
            key: "btnClover",
            tooltip: "Clover",
            value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/clover.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.DIAMOND,
            key: "btnDiamond",
            tooltip: "Diamond",
            value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/diamond.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.HEART,
            key: "btnHeart",
            tooltip: "Heart",
            value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/hearts.png" width="40" height="40" style="vertical-align: inherit;" />'
        },
        {
            id: Announce.SPADE,
            key: "btnSpade",
            tooltip: "Spade",
            value: '<img src="' + context.themeBuildPath + '/images/icons/Suites/symbol-of-spades.png" width="40" height="40" style="vertical-align: inherit;" />'
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
    
    getPlayers(): Array<Object>
    {
        return this.Players;
    }
    
    getAnnounceSymbols(): Array<Object>
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
