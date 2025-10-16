enum GameState
{
    opponentConnectWaiting,
    firstThrow,
    playing,
    requestedDoubling,
    ended,
    
    // Card Games States
    firstBid,
    bidding,
    firstRound,
    roundEnded
}

export default GameState;
