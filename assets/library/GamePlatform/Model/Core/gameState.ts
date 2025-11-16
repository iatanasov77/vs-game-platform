enum GameState
{
    opponentConnectWaiting,
    firstThrow,
    playing,
    requestedDoubling,
    ended,
    
    // Chess States
    firstMove,
        
    // Card Games States
    firstBid,
    bidding,
    firstRound,
    roundEnded
}

export default GameState;
