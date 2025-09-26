<?php namespace App\Component\Rules\CardGame;


class GameResult
{
    public PlayerPosition $Winner =>
            this.SouthNorthPoints > this.EastWestPoints
            ? PlayerPosition.SouthNorthTeam
            : PlayerPosition.EastWestTeam;
    
    public int $RoundsPlayed;
    
    public int $SouthNorthPoints;
    
    public int $EastWestPoints;
}
