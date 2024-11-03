<?php namespace App\Component\Dto;

use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;
use App\Component\Type\PlayerColor;
use App\Component\Rules\Backgammon\Game;
use App\Component\Rules\Backgammon\Player;
use App\Component\Rules\Backgammon\Point;
use App\Component\Rules\Backgammon\Checker;
use App\Component\Rules\Backgammon\Dice;
use App\Component\Rules\Backgammon\Move;

final class Mapper
{
    public static function GameToDto( Game $game ): GameDto
    {
        $gameDto = new GameDto();
        $gameDto->id = $game->Id;
        $gameDto->blackPlayer = self::PlayerToDto( $game->BlackPlayer );
        $gameDto->whitePlayer = self::PlayerToDto( $game->WhitePlayer );
        $gameDto->currentPlayer = $game->CurrentPlayer;
        $gameDto->playState = $game->PlayState;
        
        $gameDto->points = $game->Points->map(
            function( $entry ) {
                return self::PointToDto( $entry );
            }
        );
        
        $gameDto->validMoves = $game->ValidMoves->map(
            function( $entry ) {
                return self::MoveToDto( $entry );
            }
        );
        
        $gameDto->thinkTime = Game::ClientCountDown - (
            ( new \DateTime( 'now' ) )->getTimestamp() - $game->ThinkStart->getTimestamp()
        );
        
        $gameDto->goldMultiplier    = $game->GoldMultiplier;
        $gameDto->isGoldGame        = $game->IsGoldGame;
        $gameDto->lastDoubler       = $game->LastDoubler;
        $gameDto->stake             = \intval( $game->Stake );
        
        return $gameDto;
    }
    
    public static function PlayerToDto( Player $player ): PlayerDto
    {
        $playerDto = new PlayerDto();
        // Do not mapp id, it should never be sent to opponent.
        $playerDto->playerColor = $player->PlayerColor;
        $playerDto->name = $player->Name;
        $playerDto->pointsLeft = $player->PointsLeft;
        $playerDto->elo = $player->Elo;
        $playerDto->gold = $player->Gold;
        $playerDto->photoUrl = $player->Photo;
        
        return $playerDto;
    }
    
    public static function PointToDto( Point $point ): PointDto
    {
        $pointDto = new PointDto();
        $pointDto->blackNumber = $point->BlackNumber;
        $pointDto->whiteNumber = $point->WhiteNumber;
        $pointDto->checkers = $point->Checkers->map(
            function( $entry ) {
                return self::CheckerToDto( $entry );
            }
        ); // ->toArray();
        
        return $pointDto;
    }
    
    public static function CheckerToDto( Checker $checker ): CheckerDto
    {
        $checkerDto = new CheckerDto();
        $checkerDto->color = $checker->Color;
        
        return $checkerDto;
    }
    
    public static function DiceToDto( Dice $dice ): DiceDto
    {
        $diceDto = new DiceDto();
        $diceDto->used = $dice->Used;
        $diceDto->value = $dice->Value;
        
        return $diceDto;
    }
    
    public static function MoveToDto( Move $move ): MoveDto
    {
        $moveDto = new MoveDto();
        $moveDto->color = $move->Color;
        $moveDto->from = $move->From->GetNumber( $move->Color );
        $moveDto->to = $move->To->GetNumber( $move->Color );
        // recursing up in move tree
        $moveDto->nextMoves = $move->NextMoves->map(
            function( $entry ) {
                return self::MoveToDto( $entry );
            }
        )->toArray();
        
        return $moveDto;
    }
    
    public static function MoveToMove( MoveDto $dto, Game $game ): Move
    {
        $color = $dto->color;
        
        $move   = new Move();
        $move->Color = $dto->color;
        $move->From = $game->Points->filter(
            function( $entry ) use( $dto, $color ) {
                return $entry->GetNumber( $color ) == $dto->from;
            }
        )->first();
        $move->To = $game->Points->filter(
            function( $entry ) use( $dto, $color ) {
                return $entry->GetNumber( $color ) == $dto->to;
            }
        )->first();
        
        return $move;
    }
    
    public static function UserToDto( UserInterface $dbUser ): UserDto
    {
        $userDto    = new UserDto();
        $userDto->email = $dbUser->Email;
        $userDto->name = $dbUser->Name;
        $userDto->id = $dbUser->Id;
        $userDto->photoUrl = $dbUser->PhotoUrl;
        $userDto->socialProvider = $dbUser->SocialProvider;
        //$userDto->socialProviderId = $dbUser->ProviderId; // Feels more secure not to send this to the client.
        
        return $userDto;
    }
}
