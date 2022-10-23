import React, { useEffect, useState, createContext, useContext } from "react";

import Announce from '../../../library/GamePlatform/CardGameAnnounce/Announce';

export const GameContext = createContext();

export const GameProvider = ({
    children,
}) => {
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
    
    return (
        <GameContext.Provider value={{
            getAnnounceSymbols
        }}>
            {children}
        </GameContext.Provider>
    );
}
