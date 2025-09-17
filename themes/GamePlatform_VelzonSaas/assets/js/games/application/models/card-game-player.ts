import CardGamePlayerModel from '_@/GamePlatform/Model/CardGamePlayerModel';

class PlayerSouth implements CardGamePlayerModel
{
    id = 'bottom';
    announce = null;
}

class PlayerEast implements CardGamePlayerModel
{
    id = 'right';
    announce = null;
}

class PlayerNorth implements CardGamePlayerModel
{
    id = 'top';
    announce = null;
}

class PlayerWest implements CardGamePlayerModel
{
    id = 'left';
    announce = null;
}

var Players: Array<CardGamePlayerModel>  = [
    new PlayerSouth(),
    new PlayerEast(),
    new PlayerNorth(),
    new PlayerWest()
];

export function GetPlayers(): Array<CardGamePlayerModel>
{
    return Players;
}

export function GetPlayerAnnounce( playerId: String )
{
    return Players.find( ( x: any ) => x.id === playerId )?.announce;
}

export function SetPlayerAnnounce( playerId: String, announceId: any )
{
    let player  = Players.find( ( x: any ) => x.id === playerId );
    if ( player ) {
        player.announce = announceId;
    }
}
