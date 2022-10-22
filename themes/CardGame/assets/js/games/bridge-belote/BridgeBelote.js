import React, { useEffect, useState } from 'react';

import GameStatistics from './components/GameStatistics';
import GameBoard from './components/GameBoard';

const BridgeBelote = () => {

    const announceSymbols = [
        { key: "btnClover", value: <img src="/build/card-game/images/icons/Suites/clover.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { key: "btnDiamond", value: <img src="/build/card-game/images/icons/Suites/diamond.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { key: "btnHeart", value: <img src="/build/card-game/images/icons/Suites/hearts.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { key: "btnSpade", value: <img src="/build/card-game/images/icons/Suites/symbol-of-spades.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /> },
        { key: "btnBezKoz", value: <span className="announce-button">a</span> },
        { key: "btnVsichkoKoz", value: <span className="announce-button">j</span> },
        { key: "btnPass", value: <span className="announce-button">pass</span> }
    ];

    return (
        <div className="row">
            <div className="col-4">
                <GameStatistics />
            </div>
            
            <div className="col-xs-12 col-12">
                <GameBoard announceSymbols={announceSymbols} />
            </div>
        </div>
    );
}

export default BridgeBelote;
