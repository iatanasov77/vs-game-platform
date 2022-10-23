import React, { useState, useEffect, useContext } from 'react';

import { GameContext } from '../contexts/GameContext';
import BeloteCardGame from '../../../library/GamePlatform/Game/BeloteCardGame';
import Announce from '../../../library/GamePlatform/CardGameAnnounce/Announce';

const BridgeBeloteGameBoard = () => {
    
    const { getAnnounceSymbols } = useContext( GameContext );
    
    useEffect( () => {
        game.initBoard();
        
        initAnnounceButtons();
    }, [] );
  
    function initAnnounceButtons()
    {
        $( '#btnClover' ).attr( 'data-announce', Announce.CLOVER );
        $( '#btnDiamond' ).attr( 'data-announce', Announce.DIAMOND );
        $( '#btnHeart' ).attr( 'data-announce', Announce.HEART );
        $( '#btnSpade' ).attr( 'data-announce', Announce.SPADE );
        $( '#btnBezKoz' ).attr( 'data-announce', Announce.BEZ_KOZ );
        $( '#btnVsichkoKoz' ).attr( 'data-announce', Announce.VSICHKO_KOZ );
        $( '#btnPass' ).attr( 'data-announce', Announce.PASS );
    }
    
    function onStartGame( event )
    {
        event.preventDefault();
        
        game.startGame();
    }
    
    /**
     * MAIN APPLICATION
     */
    let game    = new BeloteCardGame( '#card-table' );
    
    let announceSymbols = getAnnounceSymbols();
    let announceButtons = announceSymbols.map( ( icon, index ) => (
        <a key={ `announce-button-${index}` } href="{undefined}" id={ icon.key }>
            { icon.value }
        </a>
    ));
    
    const cloverIcon = announceSymbols.find( ({ id }) => id === Announce.CLOVER ).value;
    
    return (
        <div align="center" style={{width: "950px", height: "800px"}}>
            <div id="card-table">
                <div className="leftPlayer">
                    <div id="lefthand" className="playerCards float-start"></div>
                    <div className="playerAnnounce float-end" style={{position: "relative", top: "100px"}}>
                        <span className="announceNumber">1.</span><br />
                        <span className="announceSymbol">{ cloverIcon }</span>
                    </div>
                </div>
                <div className="topPlayer">
                    <div id="upperhand" className="playerCards"></div>
                    <div className="playerAnnounce text-center align-middle" style={{position: "relative", top: "100px"}}>
                        <span className="announceNumber">2.</span>&nbsp;&nbsp;
                        <span className="announceSymbol">{ cloverIcon }</span>
                    </div>
                </div>
                <div className="rightPlayer">
                    <div id="righthand" className="playerCards"></div>
                    <div className="playerAnnounce float-start" style={{position: "relative", top: "100px"}}>
                        <span className="announceNumber">3.</span><br />
                        <span className="announceSymbol">{ cloverIcon }</span>
                    </div>
                </div>
                <div className="bottomPlayer">
                    <div id="lowerhand" className="playerCards"></div>
                    <div className="playerAnnounce text-center align-middle" style={{position: "relative", top: "0"}}>
                        <span className="announceNumber">4.</span>&nbsp;&nbsp;
                        <span className="announceSymbol">{ cloverIcon }</span>
                    </div>
                </div>
            </div>
            
            <div id="card-actions">
                <div className="p-2 float-start">
                    <a className="btn btn-primary" id="btnStartGame" onClick={ event => onStartGame( event ) }>
                        Start Game
                    </a>
                </div>
                <div className="p-2 float-end">
                    <div id="AnnounceContainer">
                        { announceButtons }
                    </div>
                </div>
            </div>
        </div>
    );
}

export default BridgeBeloteGameBoard;
