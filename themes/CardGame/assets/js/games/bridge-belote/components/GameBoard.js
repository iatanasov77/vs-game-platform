import React, { useState, useEffect } from 'react';

import BeloteCardGame from '../../../library/GamePlatform/Game/BeloteCardGame';
import Announce from '../../../library/GamePlatform/CardGameAnnounce/Announce';

const GameBoard = ( {announceSymbols} ) => {
    
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
    
    let announceButtons = announceSymbols.map( ( icon, index ) => (
        <a key={ `announce-button-${index}` } href="{undefined}" id={ icon.key }>
            { icon.value }
        </a>
    ));
    
    return (
        <div align="center" style={{width: "950px", height: "800px"}}>
            <div id="card-table">
                <div className="leftPlayer">
                    <div id="lefthand" className="playerCards float-start"></div>
                    <div className="playerAnnounce float-end" style={{position: "relative", top: "100px"}}>
                        <span className="announceNumber">1.</span><br />
                        <span className="announceSymbol"><img src="/build/card-game/images/icons/Suites/clover.png" width="40" height="40" style={{ verticalAlign: "inherit" }} /></span>
                    </div>
                </div>
                <div id="upperhand" className="upperPlayer"></div>
                <div id="righthand" className="rightPlayer"></div>
                <div id="lowerhand" className="lowerPlayer"></div>
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

export default GameBoard;
