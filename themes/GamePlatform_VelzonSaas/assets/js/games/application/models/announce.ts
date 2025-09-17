import CardGameAnnounceSymbolModel from '_@/GamePlatform/Model/CardGameAnnounceSymbolModel';
import Announce from '_@/GamePlatform/CardGameAnnounce/Announce';

class AnnounceClover implements CardGameAnnounceSymbolModel
{
    id = Announce.CLOVER;
    key = "btnClover";
    tooltip = "Clover";
    value = '<i class="fi fi-sr-club"></i>';
}

class AnnounceDiamond implements CardGameAnnounceSymbolModel
{
    id = Announce.DIAMOND;
    key = "btnDiamond";
    tooltip = "Diamond";
    value = '<i class="fi fi-sr-card-diamond"></i>';
}

class AnnounceHeart implements CardGameAnnounceSymbolModel
{
    id = Announce.HEART;
    key = "btnHeart";
    tooltip = "Heart";
    value = '<i class="fi fi-sr-heart"></i>';
}

class AnnounceSpade implements CardGameAnnounceSymbolModel
{
    id = Announce.SPADE;
    key = "btnSpade";
    tooltip = "Spade";
    value = '<i class="fi fi-sr-spade"></i>';
}

class AnnounceBezKoz implements CardGameAnnounceSymbolModel
{
    id = Announce.BEZ_KOZ;
    key = "btnBezKoz";
    tooltip = "Bez Koz";
    value = 'a';
}

class AnnounceVsichkoKoz implements CardGameAnnounceSymbolModel
{
    id = Announce.VSICHKO_KOZ;
    key = "btnVsichkoKoz";
    tooltip = "Vsichko Koz";
    value = 'j';
}

class AnnounceKontra implements CardGameAnnounceSymbolModel
{
    id = Announce.KONTRA;
    key = "btnKontra";
    tooltip = "Kontra";
    value = 'kr';
}

class AnnounceReKontra implements CardGameAnnounceSymbolModel
{
    id = Announce.RE_KONTRA;
    key = "btnReKontra";
    tooltip = "Re-Kontra";
    value = 're-kr';
}

class AnnouncePass implements CardGameAnnounceSymbolModel
{
    id = Announce.PASS;
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

export function GetAnnounceSymbol( symbolId: String )
{
    return AnnounceSymbols.find( ( x: any ) => x.id === symbolId );
}
