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
    firstRound
}

export default GameState;
