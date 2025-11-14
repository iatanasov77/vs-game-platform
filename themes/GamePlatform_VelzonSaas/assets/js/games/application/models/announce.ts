import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';
import BidType from '_@/GamePlatform/Model/CardGame/bidType';

class AnnounceClover implements CardGameAnnounceSymbolModel
{
    id = BidType.Clubs;
    key = "btnClover";
    tooltip = "Clover";
    value = '<i class="fi fi-sr-club"></i>';
}

class AnnounceDiamond implements CardGameAnnounceSymbolModel
{
    id = BidType.Diamonds;
    key = "btnDiamond";
    tooltip = "Diamond";
    value = '<i class="fi fi-sr-card-diamond"></i>';
}

class AnnounceHeart implements CardGameAnnounceSymbolModel
{
    id = BidType.Hearts;
    key = "btnHeart";
    tooltip = "Heart";
    value = '<i class="fi fi-sr-heart"></i>';
}

class AnnounceSpade implements CardGameAnnounceSymbolModel
{
    id = BidType.Spades;
    key = "btnSpade";
    tooltip = "Spade";
    value = '<i class="fi fi-sr-spade"></i>';
}

class AnnounceBezKoz implements CardGameAnnounceSymbolModel
{
    id = BidType.NoTrumps;
    key = "btnBezKoz";
    tooltip = "Bez Koz";
    value = 'a';
}

class AnnounceVsichkoKoz implements CardGameAnnounceSymbolModel
{
    id = BidType.AllTrumps;
    key = "btnVsichkoKoz";
    tooltip = "Vsichko Koz";
    value = 'j';
}

class AnnounceKontra implements CardGameAnnounceSymbolModel
{
    id = BidType.Double;
    key = "btnKontra";
    tooltip = "Kontra";
    value = 'kr';
}

class AnnounceReKontra implements CardGameAnnounceSymbolModel
{
    id = BidType.ReDouble;
    key = "btnReKontra";
    tooltip = "Re-Kontra";
    value = 're-kr';
}

class AnnouncePass implements CardGameAnnounceSymbolModel
{
    id = BidType.Pass;
    key = "btnPass";
    tooltip = "Pass";
    value = '<span class="announce-button">pass</span>';
}

var AnnounceSymbols: Array<CardGameAnnounceSymbolModel> = [
    new AnnounceClover(),
    new AnnounceDiamond(),
    new AnnounceHeart(),
    new AnnounceSpade(),
    new AnnounceBezKoz(),
    new AnnounceVsichkoKoz(),
    new AnnounceKontra(),
    new AnnounceReKontra(),
    new AnnouncePass()
];

export function GetAnnounceSymbols(): Array<CardGameAnnounceSymbolModel>
{
    return AnnounceSymbols;
}

export function GetAnnounceSymbol( symbolId: BidType )
{
    return AnnounceSymbols.find( ( x: any ) => x.id === symbolId );
}
