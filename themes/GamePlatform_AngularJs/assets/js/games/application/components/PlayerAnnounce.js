import React, { useEffect, useState, useContext } from 'react';

import Announce from '../../../../../../../assets/library/GamePlatform/CardGameAnnounce/Announce';

import { GameContext } from '../contexts/GameContext';
import * as GameEvents from '../../../../../../../assets/library/GamePlatform/Game/GameEvents';

const PlayerAnnounce = ( {player} ) => {

    const { getAnnounceSymbol, getAnnounce, setAnnounce } = useContext( GameContext );
    const [announceIcon, setAnnounceIcon] = useState( null );
    
    useEffect( () => {
        listenForGameEvents();
    }, [] );
    
    let position    = player.id;
    let className   = 'playerAnnounce';
    let styles      = {
        position: "relative",
        top: "100px"
    };
    
    switch ( player.id ) {
        case 'left':
            className   += ' float-end';
            
            break;
        case 'top':
            className   += ' text-center align-middle';
            
            break;
        case 'right':
            className   += ' float-start';
            
            break;
        case 'bottom':
            className   += ' text-center align-middle';
            styles.top  = 0;
            
            break;
    }
    
    function listenForGameEvents()
    {
        $( "#" + player.containerId ).get( 0 ).addEventListener( GameEvents.PLAYER_ANNOUNCE_EVENT_NAME, ( event ) => {
            const { announceId }    = event.detail;
            
            setAnnounce( player.id, announceId );
            if ( position === player.id ) {
                //alert( announceId );
                setAnnounceIcon( getAnnounceSymbol( announceId ).value );
            }
        });
    }
    
    return (
        <div className={className} style={styles}>
            <span className="announceNumber">{ player.name }</span><br />
            <span className="announceSymbol">{ announceIcon }</span>
        </div>
    );
}

export default PlayerAnnounce;
