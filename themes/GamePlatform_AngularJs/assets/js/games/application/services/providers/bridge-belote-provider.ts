import { Injectable } from '@angular/core';

import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';

@Injectable({
    providedIn: 'root'
})
export class BridgeBeloteProvider
{
    Players: any    = [
        { id: 'left', announce: null },
        { id: 'top', announce: null },
        { id: 'right', announce: null },
        { id: 'bottom', announce: null }
    ];
    
    AnnounceSymbols: any   = [
        { id: Announce.CLOVER, key: "btnClover", value: '<img src="/build/game-platform-angularjs/images/icons/Suites/clover.png" width="40" height="40" style="vertical-align: inherit;" />' },
        { id: Announce.DIAMOND, key: "btnDiamond", value: '<img src="/build/game-platform-angularjs/images/icons/Suites/diamond.png" width="40" height="40" style="vertical-align: inherit;" />' },
        { id: Announce.HEART, key: "btnHeart", value: '<img src="/build/game-platform-angularjs/images/icons/Suites/hearts.png" width="40" height="40" style="vertical-align: inherit;" />' },
        { id: Announce.SPADE, key: "btnSpade", value: '<img src="/build/game-platform-angularjs/images/icons/Suites/symbol-of-spades.png" width="40" height="40" style="vertical-align: inherit;" />' },
        { id: Announce.BEZ_KOZ, key: "btnBezKoz", value: '<span class="announce-button">a</span>' },
        { id: Announce.VSICHKO_KOZ, key: "btnVsichkoKoz", value: '<span class="announce-button">j</span>' },
        { id: Announce.KONTRA, key: "btnKontra", value: '<span class="announce-button">kra</span>' },
        { id: Announce.RE_KONTRA, key: "btnReKontra", value: '<span class="announce-button">re-kra</span>' },
        { id: Announce.PASS, key: "btnPass", value: '<span class="announce-button">pass</span>' }
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
