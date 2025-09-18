enum GameState
{
    opponentConnectWaiting,
    firstThrow,
    playing,
    requestedDoubling,
    ended,
    
    // Card Games States
    firstBid,
    bidding
}

export default GameState;
