import React, { useState, useEffect, useContext } from 'react';

import { GameContext } from '../contexts/GameContext';
import PlayerAnnounce from './PlayerAnnounce';
import BeloteCardGame from '../../../library/GamePlatform/Game/BeloteCardGame';
import * as GameEvents from '../../../library/GamePlatform/Game/GameEvents';
import Announce from '../../../library/GamePlatform/CardGameAnnounce/Announce';

const BridgeBeloteGameBoard = () => {
    
    const { getAnnounceSymbols, setAnnounce } = useContext( GameContext );
    
    useEffect( () => {
        game.initBoard();
        
        initAnnounceButtons();
        $( '#AnnounceContainer' ).hide();
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
    
    let playerContainers    = game.players.map( ( player, index ) => (
        <div key={ `player-container-${index}` }
            className={ player.containerId.charAt( 0 ).toLowerCase() + player.containerId.slice( 1 ) }
            id={ player.containerId }
        >
            <div id={ player.cardsId } className="playerCards float-start"></div>
            <PlayerAnnounce player={ player } />
        </div>
    ));
    
    let announceSymbols     = getAnnounceSymbols();
    let announceButtons     = announceSymbols.map( ( icon, index ) => (
        <a key={ `announce-button-${index}` } href="{undefined}" id={ icon.key }>
            { icon.value }
        </a>
    ));
    
    return (
        <div align="center" style={{width: "950px", height: "800px"}}>
            <div id="card-table">
                { playerContainers }
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
