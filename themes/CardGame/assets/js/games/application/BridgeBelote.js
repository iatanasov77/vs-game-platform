import React from 'react';

import { GameProvider } from './contexts/GameContext';
import BridgeBeloteGameStatistics from './components/BridgeBeloteGameStatistics';
import BridgeBeloteGameBoard from './components/BridgeBeloteGameBoard';

const BridgeBelote = () => {

    return (
        <GameProvider>
            <div className="row">
                <div className="col-4">
                    <BridgeBeloteGameStatistics />
                </div>
                
                <div className="col-xs-12 col-12">
                    <BridgeBeloteGameBoard />
                </div>
            </div>
        </GameProvider>
    );
}

export default BridgeBelote;
