enum ActionNames {
    gameCreated,
    dicesRolled,
    movesMade,
    gameEnded,
    opponentMove,
    undoMove,
    connectionInfo,
    gameRestore,
    resign,
    exitGame,
    requestedDoubling,
    acceptedDoubling,
    rolled,
    requestHint,
    hintMoves,
    
    // Card Game Actions
    biddingStarted,
    bid,
    playCard,
    
    serverWasTerminated
}

export default ActionNames;
