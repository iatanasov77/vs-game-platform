import React from 'react';

import './CardGame.scss'
import './BridgeBelote.scss'

import { GameProvider } from './contexts/GameContext';
import BridgeBeloteGameStatistics from './components/BridgeBeloteGameStatistics';
import BridgeBeloteGameBoard from './components/BridgeBeloteGameBoard';

const BridgeBelote = () => {

    return (
        <GameProvider>
            <div className="row">
                <div className="col-2">
                    <BridgeBeloteGameStatistics />
                </div>
                
                <div className="col-xs-10 col-10">
                    <BridgeBeloteGameBoard />
                </div>
            </div>
        </GameProvider>
    );
}

export default BridgeBelote;
