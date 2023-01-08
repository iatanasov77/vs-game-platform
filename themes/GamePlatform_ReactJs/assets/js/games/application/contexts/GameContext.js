import React, { useState, createContext } from "react";

import Announce from '../../../../../../../assets/library/GamePlatform/CardGameAnnounce/Announce';

export const GameContext = createContext();
export const GameProvider = ({
    children,
}) => {

    const [players, setPlayers] = useState([
        { id: 'left', announce: null },
        { id: 'top', announce: null },
        { id: 'right', announce: null },
        { id: 'bottom', announce: null }
    ]);

    const AnnounceSymbols = [
        { id: Announce.CLOVER, key: "btnClover", value: <img src="/build/card-game/images/icons/Suites/clover.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { id: Announce.DIAMOND, key: "btnDiamond", value: <img src="/build/card-game/images/icons/Suites/diamond.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { id: Announce.HEART, key: "btnHeart", value: <img src="/build/card-game/images/icons/Suites/hearts.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { id: Announce.SPADE, key: "btnSpade", value: <img src="/build/card-game/images/icons/Suites/symbol-of-spades.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { id: Announce.BEZ_KOZ, key: "btnBezKoz", value: <span className="announce-button">a</span> },
        { id: Announce.VSICHKO_KOZ, key: "btnVsichkoKoz", value: <span className="announce-button">j</span> },
        { id: Announce.PASS, key: "btnPass", value: <span className="announce-button">pass</span> }
    ];
    
    const getAnnounceSymbols = () => {
        return AnnounceSymbols;
    };
    
    const getAnnounceSymbol = ( symboId ) => {
        return AnnounceSymbols.find( ({ id }) => id === symboId );
    };
    
    const getAnnounce = ( playerId ) => {
        return players.find( ({ id }) => id === playerId ).announce;
    };
    
    const setAnnounce = ( playerId, announceId ) => {
        let player  = players.find( ({ id }) => id === playerId );
        player.announce = announceId;
    };
    
    return (
        <GameContext.Provider value={{
            players: players,
            getAnnounceSymbols,
            getAnnounceSymbol,
            getAnnounce,
            setAnnounce
        }}>
            {children}
        </GameContext.Provider>
    );
}
