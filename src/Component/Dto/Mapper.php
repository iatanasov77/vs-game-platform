<?php namespace App\Component\Dto;

use Doctrine\Common\Collections\ArrayCollection;
use Vankosoft\UsersBundle\Model\Interfaces\UserInterface;

use App\Component\Rules\BoardGame\Game as BoardGame;
use App\Component\Rules\BoardGame\BackgammonGame;
use App\Component\Rules\BoardGame\ChessGame;
use App\Component\Rules\BoardGame\Player as BoardGamePlayer;
use App\Component\Rules\BoardGame\Point;
use App\Component\Rules\BoardGame\Checker;
use App\Component\Rules\BoardGame\Dice;
use App\Component\Rules\BoardGame\Move;
use App\Component\Rules\BoardGame\ChessMove;
use App\Component\Rules\BoardGame\ChessSquare;
use App\Component\Rules\BoardGame\ChessPiece;
use App\Component\Rules\BoardGame\ChessSide;

use App\Component\Rules\CardGame\Game as CardGame;
use App\Component\Rules\CardGame\Card;
use App\Component\Rules\CardGame\Bid;
use App\Component\Rules\CardGame\Announce;
use App\Component\Rules\CardGame\CardExtensions;
use App\Component\Rules\CardGame\GameMechanics\RoundResult;
use App\Component\Type\PlayerPosition;
use App\Component\Type\BidType;

final class Mapper
{
    public static function BoardGameToDto( BoardGame $game ): BoardGameDto
    {
        $gameDto = new BoardGameDto();
        $gameDto->id = $game->Id;
        $gameDto->blackPlayer = self::BoardGamePlayerToDto( $game->BlackPlayer );
        $gameDto->whitePlayer = self::BoardGamePlayerToDto( $game->WhitePlayer );
        $gameDto->currentPlayer = $game->CurrentPlayer;
        $gameDto->playState = $game->PlayState;
        
        if ( $game instanceof BackgammonGame ) {
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
        }
        
        if ( $game instanceof ChessGame ) {
            $gameDto->squares = $game->Squares->map(
                function( $entry ) {
                    return self::ChessSquareToDto( $entry );
                }
            );
        }
        
        $gameDto->thinkTime = BoardGame::ClientCountDown - (
            ( new \DateTime( 'now' ) )->getTimestamp() - $game->ThinkStart->getTimestamp()
        );
        
        $gameDto->goldMultiplier    = $game->GoldMultiplier;
        $gameDto->isGoldGame        = $game->IsGoldGame;
        $gameDto->lastDoubler       = $game->LastDoubler;
        $gameDto->stake             = \intval( $game->Stake );
        
        return $gameDto;
    }
    
    public static function BoardGamePlayerToDto( BoardGamePlayer $player ): PlayerDto
    {
        $playerDto = new PlayerDto();
        
        // Do not mapp id, it should never be sent to opponent.
        $playerDto->name = $player->Name;
        
        $playerDto->playerColor = $player->PlayerColor;
        $playerDto->pointsLeft = $player->PointsLeft;
        
        $playerDto->elo = $player->Elo;
        $playerDto->gold = $player->Gold;
        $playerDto->photoUrl = $player->Photo;
        
        $playerDto->isAi = $player->IsAi();
        
        return $playerDto;
    }
    
    public static function PointToDto( Point $point ): PointDto
    {
        $pointDto = new PointDto();
        $pointDto->blackNumber = $point->BlackNumber;
        $pointDto->whiteNumber = $point->WhiteNumber;
        $pointDto->checkers = new ArrayCollection( $point->Checkers->map(
            function( $entry ) {
                return self::CheckerToDto( $entry );
            }
        )->getValues() );
        
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
        ); // ->toArray();
        
        return $moveDto;
    }
    
    public static function MoveToMove( MoveDto $dto, BoardGame $game ): Move
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
    
    public static function CardGameToDto( CardGame $game ): CardGameDto
    {
        $gameDto = new CardGameDto();
        $gameDto->id = $game->Id;
        
        $gameDto->players = self::CardGamePlayersToDto( $game->Players );
        
        $validBids = $game->AvailableBids->map(
            function( $entry ) {
                return self::BidToDto( $entry );
            }
        )->toArray();
        $gameDto->validBids = \array_values( $validBids );
        
        $validCards = $game->ValidCards->map(
            function( $entry ) use ( $game ) {
                return self::CardToDto( $entry, $game->CurrentPlayer );
            }
        )->toArray();
        $gameDto->validCards = \array_values( $validCards );
        
        $gameDto->contract = $game->CurrentContract ? self::BidToDto( $game->CurrentContract ) : null;
        
        $gameDto->currentPlayer = $game->CurrentPlayer;
        $gameDto->playState = $game->PlayState;
        
        $gameDto->FirstToPlayInTheRound = $game->firstInRound;
        $gameDto->RoundNumber = $game->roundNumber;
        $gameDto->TrickNumber = $game->trickNumber;
        
        $gameDto->thinkTime = CardGame::ClientCountDown - (
            ( new \DateTime( 'now' ) )->getTimestamp() - $game->ThinkStart->getTimestamp()
        );
        
        $gameDto->goldMultiplier    = $game->GoldMultiplier;
        $gameDto->isGoldGame        = $game->IsGoldGame;
        
        return $gameDto;
    }
    
    public static function CardGamePlayersToDto( array $players ): array
    {
        $playersDto = [];
        foreach ( $players as $player ) {
            $playerDto = new PlayerDto();
            
            // Do not mapp id, it should never be sent to opponent.
            $playerDto->name = $player->Name;
            
            $playerDto->playerPosition = $player->PlayerPosition;
            
            $playerDto->elo = $player->Elo;
            $playerDto->gold = $player->Gold;
            $playerDto->photoUrl = $player->Photo;
            
            $playerDto->isAi = $player->IsAi();
            
            $playersDto[] = $playerDto;
        }
        
        return $playersDto;
    }
    
    public static function CardToDto( Card $card, PlayerPosition $position = PlayerPosition::Neither ): CardDto
    {
        $cardDto = new CardDto();
        $cardDto->Suit = $card->Suit;
        $cardDto->Type = $card->Type;
        
        $cardDto->position = $position;
        $cardDto->cardIndex = \strtolower( CardExtensions::TypeToString( $card->Type ) . CardExtensions::SuitToString( $card->Suit ) );
        
        return $cardDto;
    }
    
    public static function BidToDto( Bid $bid ): BidDto
    {
        $bidDto = new BidDto();
        
        $bidDto->Player = $bid->Player;
        $bidDto->KontraPlayer = $bid->KontraPlayer;
        $bidDto->ReKontraPlayer = $bid->ReKontraPlayer;
        
        $bidDto->Type = BidType::fromBitMaskValue( $bid->Type->get() )->value();
        
        return $bidDto;
    }
    
    public static function AnnounceToDto( Announce $announce, PlayerPosition $position = PlayerPosition::Neither ): AnnounceDto
    {
        $announceDto = new AnnounceDto();
        $announceDto->Type = $announce->Type;
        $announceDto->Player = $position;
        
        return $announceDto;
    }
    
    public static function RoundResultToDto( RoundResult $score ): BridgeBeloteScoreDto
    {
        $scoreDto = new BridgeBeloteScoreDto();
        
        $scoreDto->SouthNorthPoints = $score->SouthNorthPoints;
        $scoreDto->SouthNorthTotalInRoundPoints = $score->SouthNorthTotalInRoundPoints;
        $scoreDto->EastWestPoints = $score->EastWestPoints;
        $scoreDto->EastWestTotalInRoundPoints = $score->EastWestTotalInRoundPoints;
        
        return $scoreDto;
    }
    
    public static function ChessSquareToDto( ChessSquare $square ): ChessSquareDto
    {
        $chessSquareDto = new ChessSquareDto();
        $chessSquareDto->Rank = $square->Rank;
        $chessSquareDto->File = $square->File;
        $chessSquareDto->Piece = self::ChessPieceToDto( $square->Piece );
        
        return $chessSquareDto;
    }
    
    public static function ChessPieceToDto( ?ChessPiece $piece ): ?ChessPieceDto
    {
        if ( ! $piece ) {
            return null;
        }
        
        $chessPieceDto = new ChessPieceDto();
        $chessPieceDto->Type = $piece->Type;
        $chessPieceDto->Side = $piece->Side->type;
        $chessPieceDto->Moves = $piece->Moves;
        
        return $chessPieceDto;
    }
    
    public static function ChessMoveToDto( ChessMove $move ): ChessMoveDto
    {
        $moveDto = new ChessMoveDto();
        $moveDto->color = $move->Color;
        $moveDto->type = $move->Type;
        $moveDto->from = "{$move->From}";
        $moveDto->to = "{$move->To}";
        
        /*
        $moveDto->piece = $move->Piece->Type;
        $moveDto->capturedPiece =  $move->CapturedPiece ? $move->CapturedPiece->Type : null;
        $moveDto->promoPiece =  $move->PromoPiece ?$move->PromoPiece->Type : null;
        $moveDto->enpassantPiece =  $move->EnPassantPiece ? $move->EnPassantPiece->Type : null;
        */
        
        // recursing up in move tree
        $moveDto->nextMoves = $move->NextMoves->map(
            function( $entry ) {
                return self::ChessMoveToDto( $entry );
            }
        ); // ->toArray();
        
        return $moveDto;
    }
    
    public static function ChessMoveToChessMove( ChessMoveDto $dto, BoardGame $game ): ChessMove
    {
        $move   = new ChessMove();
        $move->Color = $dto->color;
        $move->Type = $dto->type;
        
        $move->From = $game->Squares[$dto->from];
        $move->To = $game->Squares[$dto->to];

        /*
        $move->Piece = $dto->piece ? new ChessPiece( $dto->piece, new ChessSide( $dto->color ) ) : null;
        $move->CapturedPiece = $dto->capturedPiece ? new ChessPiece( $dto->capturedPiece, new ChessSide( $dto->color ) ) : null;
        $move->PromoPiece = $dto->promoPiece ? new ChessPiece( $dto->promoPiece, new ChessSide( $dto->color ) ) : null;
        $move->EnPassantPiece = $dto->enpassantPiece ? new ChessPiece( $dto->enpassantPiece, new ChessSide( $dto->color ) ) : null;
        */
        
        return $move;
    }
}
