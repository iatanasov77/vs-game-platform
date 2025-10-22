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
    bidMade,
    opponentBids,
    playingStarted,
    playCard,
    opponentPlayCard,
    trickEnded,
    roundEnded,
    startNewRound,
    newRoundStarted,
    
    serverWasTerminated
}

export default ActionNames;
