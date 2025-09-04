<?php namespace App\Component\Rules\CardGame;

use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;
use App\Component\Utils\Guid;
use App\Entity\GamePlayer;
use App\Component\Rules\CardGame\Context\PlayerGetBidContext;

class Player
{
    /** @var int */
    public $Id;
    
    /** @var string */
    public $Name;
    
    /** @var PlayerPosition */
    public $PlayerPosition;
    
    /** @var string */
    public $Photo;
    
    /** @var int */
    public $Gold;
    
    /**
     * Player rating system, which assigns a numerical score to players 
     * based on their performance in rated matches. 
     * 
     * @var int
     */
    public $Elo;
    
    /**
     * Do not map this to the dto. Opponnents id should never be revealed to anyone else.
     * 
     * @var Guid
     */
    public $Guid;
    
    /** @var bool */
    public $FirstMoveMade;
    
    public function __toString(): string
    {
        switch ( $this->PlayerPosition->value ) {
            case 0:
                $playerPosition = 'North';
                break;
            case 1:
                $playerPosition = 'South';
                break;
            case 2:
                $playerPosition = 'East';
                break;
            case 3:
                $playerPosition = 'West';
                break;
            default:
                $playerPosition = 'Neither';   
        }
        return $playerPosition . " player";
    }
        
    public function IsGuest(): bool
    {
        return $this->Id == Guid::Empty();
    }
    
    public function IsAi(): bool
    {
        return $this->Guid == GamePlayer::AiUser;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function GetBid( PlayerGetBidContext $context ): BidType
    {
        $this->NewRoundCheck( $context );
        
        // this.DrawLastBids(context);
        while ( true ) {
            // this.Draw(context);
            
            $availableBidsList = ["P(ass)"];
            if ( $context->AvailableBids->HasFlag( BidType::Clubs ) ) {
                $availableBidsList[] = "C(♣)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::Diamonds ) ) {
                $availableBidsList[] = "D(♦)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::Hearts ) ) {
                $availableBidsList[] = "H(♥)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::Spades ) ) {
                $availableBidsList[] = "S(♠)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::NoTrumps ) ) {
                $availableBidsList[] = "N(o)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::AllTrumps ) ) {
                $availableBidsList[] = "A(ll)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::Double ) ) {
                $availableBidsList[] = "2(double)";
            }
            
            if ( $context->AvailableBids->HasFlag( BidType::ReDouble ) ) {
                $availableBidsList[] = "4(re double)";
            }
            
            $availableBidsAsString = implode( ", ", $availableBidsList );
            ConsoleHelper.WriteOnPosition(availableBidsAsString, 0, Console.WindowHeight - 2);
            ConsoleHelper.WriteOnPosition("It's your turn! Please enter your bid: ", 0, Console.WindowHeight - 3);
            
            var playerContract = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(playerContract))
            {
                continue;
            }
            
            playerContract = playerContract.Trim();
            BidType bid;
            switch (char.ToUpper(playerContract[0]))
            {
                case 'A':
                    bid = BidType.AllTrumps;
                    break;
                case 'N':
                    bid = BidType.NoTrumps;
                    break;
                case 'S':
                    bid = BidType.Spades;
                    break;
                case 'H':
                    bid = BidType.Hearts;
                    break;
                case 'D':
                    bid = BidType.Diamonds;
                    break;
                case 'C':
                    bid = BidType.Clubs;
                    break;
                case 'P':
                    bid = BidType.Pass;
                    break;
                case '2':
                    bid = BidType.Double;
                    break;
                case '4':
                    bid = BidType.ReDouble;
                    break;
                default:
                    continue;
            }
            
            if (context.AvailableBids.HasFlag(bid))
            {
                return bid;
            }
        }
        
        
    }
    
    public IList<Announce> GetAnnounces(PlayerGetAnnouncesContext context)
    {
        this.NewRoundCheck(context);
        this.DrawLastBids(context);
        this.DrawLastPlayedCards(context, context.CurrentTrickActions);
        var availableAnnounces = context.AvailableAnnounces.ToList();
        if (!availableAnnounces.Any())
        {
            ConsoleHelper.WriteOnPosition("No card combinations available.", 0, Console.WindowHeight - 3);
            return availableAnnounces;
        }
        
        var availableCombinationsAsString = availableAnnounces.Count == 1
        ? $"You have {availableAnnounces[0]}. Press [enter] to announce it or press 0 and enter to skip it."
            : $"Press 0 to skip, 1 for {availableAnnounces[0]}, 2 for {availableAnnounces[1]} or [enter] for all";
            
            ConsoleHelper.WriteOnPosition(availableCombinationsAsString, 0, Console.WindowHeight - 2);
            ConsoleHelper.WriteOnPosition("Choose which combinations you want to announce ([enter] for all): ", 0, Console.WindowHeight - 3);
            
            var line = Console.ReadLine();
            if (string.IsNullOrWhiteSpace(line))
            {
                return availableAnnounces;
            }
            
            if (line.Trim() == "0")
            {
                return new List<Announce>();
            }
            
            if (line.Trim() == "1" && availableAnnounces.Count >= 1)
            {
                return new List<Announce> { availableAnnounces[0] };
            }
            
            if (line.Trim() == "2" && availableAnnounces.Count >= 2)
            {
                return new List<Announce> { availableAnnounces[1] };
            }
            
            return availableAnnounces;
    }
    
    public PlayCardAction PlayCard(PlayerPlayCardContext context)
    {
        this.NewRoundCheck(context);
        this.DrawLastBids(context);
        this.DrawLastPlayedCards(context, context.RoundActions);
        
        var sb = new StringBuilder();
        var allowedCardsList = context.AvailableCardsToPlay.ToList();
        for (var i = 0; i < allowedCardsList.Count; i++)
        {
            sb.AppendFormat("{0}({1}); ", i + 1, allowedCardsList[i]);
        }
        
        while (true)
        {
            ConsoleHelper.WriteOnPosition(new string(' ', 78), 0, Console.WindowHeight - 3);
            ConsoleHelper.WriteOnPosition(new string(' ', 78), 0, Console.WindowHeight - 2);
            ConsoleHelper.WriteOnPosition(sb.ToString().Trim(), 0, Console.WindowHeight - 2);
            ConsoleHelper.WriteOnPosition("It's your turn! Please select card to play: ", 0, Console.WindowHeight - 3);
            var cardIndexAsString = Console.ReadLine();
            if (int.TryParse(cardIndexAsString, out var cardIndex))
            {
                if (cardIndex > 0 && cardIndex <= allowedCardsList.Count)
                {
                    var cardToPlay = allowedCardsList[cardIndex - 1];
                    var announceBelote = false;
                    if (this.announcesService.IsBeloteAllowed(context.MyCards, context.CurrentContract.Type, context.CurrentTrickActions.ToList(), cardToPlay))
                    {
                        ConsoleHelper.WriteOnPosition(new string(' ', 78), 0, Console.WindowHeight - 3);
                        ConsoleHelper.WriteOnPosition(new string(' ', 78), 0, Console.WindowHeight - 2);
                        ConsoleHelper.WriteOnPosition(new string(' ', 78), 0, Console.WindowHeight - 1);
                        ConsoleHelper.WriteOnPosition("Y(es) / N(o)", 0, Console.WindowHeight - 2);
                        ConsoleHelper.WriteOnPosition("You have belote! Do you want to announce it? Y/N ", 0, Console.WindowHeight - 3);
                        var answer = Console.ReadLine();
                        if (!string.IsNullOrWhiteSpace(answer) && answer.Trim()[0] != 'N')
                        {
                            announceBelote = true;
                        }
                    }
                    
                    return new PlayCardAction(cardToPlay, announceBelote);
                }
            }
        }
    }
}
