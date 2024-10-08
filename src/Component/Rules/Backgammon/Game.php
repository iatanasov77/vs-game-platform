<?php namespace App\Component\Rules\Backgammon;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\System\Guid;
use App\Component\Type\GameState;
use App\Component\Type\PlayerColor;
use App\Entity\GamePlayer;

/**
 * Rules: https://www.bkgm.com/rules.html
 */
class Game
{
    /** @var string */
    public $Id;
    
    /** @var GamePlayer */
    public $BlackPlayer;
    
    /** @var GamePlayer */
    public $WhitePlayer;
    
    /** @var PlayerColor */
    public $CurrentPlayer;
    
    /** @var Collection | Point[] */
    public $Points;
    
    /** @var Collection | Dice[] */
    public $Roll;
    
    /** @var Collection | Move[] */
    public $ValidMoves;
    
    /** @var GameState */
    public $PlayState = GameState::FirstThrow;
    
    /** @var \DateTime */
    public $Created;
    
    /** @var \DateTime */
    public $ThinkStart;
    
    /** @var Collection | Point[] */
    public $Bars;
    
    /** @var int */
    public $GoldMultiplier;
    
    /** @var bool */
    public $IsGoldGame;
    
    /** @var PlayerColor */
    public $LastDoubler = null;
    
    /** @var int */
    public $Stake;
    
    /** @var int */
    const ClientCountDown = 40;
    
    /** @var int */
    const TotalThinkTime = 48;
    
    public static function Create( bool $forGold )
    {
        //die( 'EHO' );
        $game = new Game();
        
        $game->Id           = Guid::NewGuid();
        $game->Points       = new ArrayCollection();
        $game->Roll         = new ArrayCollection();
        $game->ValidMoves   = new ArrayCollection();
        
        $game->BlackPlayer = new Player();
        $game->BlackPlayer->PlayerColor = PlayerColor::Black;
        $game->BlackPlayer->Name = "Guest";
        
        $game->WhitePlayer = new Player();
        $game->BlackPlayer->PlayerColor = PlayerColor::White;
        $game->BlackPlayer->Name = "Guest";
        
        $game->Created = new \DateTime( 'now' );
        $game->PlayState = GameState::OpponentConnectWaiting;
        $game->GoldMultiplier = 1;
        $game->IsGoldGame = $forGold;
        $game->LastDoubler = null;
        
        $game->Points = new ArrayCollection(); // 24 points, 1 bar and 1 home,
        
        for ( $i = 0; $i < 26; $i++ ) {
            $point  = new Point();
            $point->BlackNumber = $i;
            $point->WhiteNumber = 25 - $i;
            
            $game->Points[] = $point;
        }
        
        $game->Bars = new ArrayCollection();
        $game->Bars[PlayerColor::Black->value] = $game->Points->first();
        $game->Bars[PlayerColor::White->value] = $game->Points->last();
        
        $game->SetStartPosition();
        $game->GoldMultiplier = 1;
        
        self::CalcPointsLeft( $game );
        
        return $game;
    }
    
    private static function CalcPointsLeft( Game &$game ): void
    {
        foreach ( $game->Points as $point ) {
            $blackCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry->Color == PlayerColor::Black;
                }
                );
            foreach ( $blackCheckers as $ckr ) {
                $game->BlackPlayer->PointsLeft += 25 - $point->BlackNumber;
            }
            
            $whiteCheckers  = $point->Checkers->filter(
                function( $entry ) {
                    return $entry->Color == PlayerColor::White;
                }
                );
            foreach ( $whiteCheckers as $ckr ) {
                $game->WhitePlayer->PointsLeft += 25 - $point->WhiteNumber;
            }
        }
    }
    
    private function OtherPlayer(): PlayerColor
    {
        return $this->CurrentPlayer == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
    }
    
    private function SetStartPosition(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
        
        $this->AddCheckers( 2, PlayerColor::Black, 1 );
        $this->AddCheckers( 2, PlayerColor::White, 1 );
        
        $this->AddCheckers( 5, PlayerColor::Black, 12 );
        $this->AddCheckers( 5, PlayerColor::White, 12 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 17 );
        $this->AddCheckers( 3, PlayerColor::White, 17 );
        
        $this->AddCheckers( 5, PlayerColor::Black, 19 );
        $this->AddCheckers( 5, PlayerColor::White, 19 );
        
        //OneMoveToVictory();
        
        //DebugBlocked();
        
        // DebugBearingOff();
        
        // AtHomeAndOtherAtBar();
    }
    
    private function AtHomeAndOtherAtBar(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 2, PlayerColor::Black, 22 );
        $this->AddCheckers( 5, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::Black, 25 );
        
        $this->AddCheckers( 2, PlayerColor::White, 19 );
        $this->AddCheckers( 2, PlayerColor::White, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        $this->AddCheckers( 2, PlayerColor::White, 22 );
        $this->AddCheckers( 2, PlayerColor::White, 23 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 0 );
        
    }
    
    private function OneMoveToVictory(): void
    {
        //Only one move to victory
        $this->AddCheckers( 14, PlayerColor::Black, 25 );
        $this->AddCheckers( 14, PlayerColor::White, 25 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 24 );
        $this->AddCheckers( 1, PlayerColor::White, 24 );
    }
    
    private function DebugBlocked(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 0 );
        $this->AddCheckers( 1, PlayerColor::White, 0 );
    }
    
    private function DebugBearingOff(): void
    {
        $this->AddCheckers( 3, PlayerColor::Black, 20 );
        $this->AddCheckers( 3, PlayerColor::White, 20 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 21 );
        $this->AddCheckers( 3, PlayerColor::White, 21 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 22 );
        $this->AddCheckers( 3, PlayerColor::White, 22 );
        
        $this->AddCheckers( 3, PlayerColor::Black, 23 );
        $this->AddCheckers( 3, PlayerColor::White, 23 );
        
        $this->AddCheckers( 2, PlayerColor::Black, 24 );
        $this->AddCheckers( 2, PlayerColor::White, 24 );
        
        $this->AddCheckers( 1, PlayerColor::Black, 19 );
        $this->AddCheckers( 1, PlayerColor::White, 19 );
    }
    
    public function ClearCheckers(): void
    {
        foreach ( $this->Points as $point ) {
            $point->Checkers->clear();
        }
    }
    
    public function IsBearingOff( PlayerColor $color ): bool
    {
        // Points that have checkers with the color asked all have higher number than 18
        $colorCheckers  = $this->Points->filter(
            function( $entry ) use ( $color ) {
                return $entry->Checkers->filter(
                    function( $entry ) use ( $color ) {
                        return $entry->Color    = $color;
                    }
                );
            }
        )->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) >= 19;
            }
        );
        
        return ! $colorCheckers->isEmpty();
    }
    
    public function AddCheckers( int $count, PlayerColor $color, int $point ): void
    {
        $checker        = new Checker();
        $checker->Color = $color;
        
        for ( $i = 0; $i < $count; $i++ ) {
            $this->Points->filter(
                function( $entry ) use ( $color, $point ) {
                    return $entry->GetNumber( $color ) == $point;
                }
            )->first()->Checkers[]  = $checker;
        }
    }
    
    public function FakeRoll( int $v1, int $v2 ): void
    {
        $this->Roll = new ArrayCollection( Dice::GetDices( $v1, $v2 )->toArray() );
        $this->SetFirstRollWinner();
    }
    
    public function SetFirstRollWinner(): void
    {
        if ( $this->PlayState == GameState::FirstThrow ) {
            if ( $this->Roll[0]->Value > $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::Black;
            } else if ( $this->Roll[0]->Value < $this->Roll[1]->Value ) {
                $this->CurrentPlayer = PlayerColor::White;
            }
            
            if ( $this->Roll[0]->Value != $this->Roll[1]->Value ) {
                $this->PlayState = GameState::Playing;
            }
        }
    }
    
    public function RollDice(): void
    {
        $this->Roll = new ArrayCollection( Dice::Roll()->toArray() );
        $this->SetFirstRollWinner();
        
//         $this->ClearMoves( $this->ValidMoves );
//         $this->_GenerateMoves( $this->ValidMoves );
    }
    
    private function ClearMoves( Collection &$moves ): void
    {
        // This will probably make it a lot easier for GC, and might even prevent memory leaks
        foreach ( $moves as $move ) {
            if ( $move->NextMoves != null && ! empty( $move->NextMoves ) ) {
                $this->ClearMoves( $move->NextMoves );
                $move->NextMoves->clear();
            }
        }
        $moves->clear();
    }
    
    public function GenerateMoves(): array
    {
        $moves = new ArrayCollection();
        $this->_GenerateMoves( $moves );
        
        // Making sure both dice are played
        if ( $moves->NextMoves->count() ) {
            $moves = $moves->filter(
                function( $entry ) {
                    return $entry->NextMoves->count() > 0;
                }
                )->toArray();
        } else if ( $moves->count() ) {
            // All moves have zero next move in this block
            // Only one dice can be use and it must be the one with highest value
            
            $currentPlayer  = $this->CurrentPlayer;
            $moves = $moves->filter(
                function( $entry ) use ( $currentPlayer ) {
                    return $entry->To->GetNumber( $currentPlayer ) - $entry->From->GetNumber( $currentPlayer );
                }
                );
            $first = $moves->getMovesOrderByDescending( $moves )->first();
            $moves->clear();
            $moves[]    = $first;
        }
        
        return $moves;
    }
    
    public function GetHome( PlayerColor $color ): Point
    {
        return $this->Points->filter(
            function( $entry ) use ( $color ) {
                return $entry->GetNumber( $color ) == 25;
            }
            )->first();
    }
    
    private function _GenerateMoves( Collection &$moves ): void
    {
        $currentPlayer  = $this->CurrentPlayer;
        $bar = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->GetNumber( $currentPlayer ) == 0;
            }
        );
        $barHasCheckers = $bar->first()->Checkers->exists(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->Color == $currentPlayer;
            }
        );
        
        foreach ( $this->getRollOrderByDescending() as $dice ) {
            if ( $dice->Used ) {
                continue;
            }
            $dice->Used = true;
            
            $points = $barHasCheckers ? $bar : $this->getPointsOrdered( $currentPlayer );
            foreach ( $points as $fromPoint ) {
                $fromPointNo = $fromPoint->GetNumber( $currentPlayer );
                if ( $fromPointNo == 25 ) {
                    continue;
                }
                
                $shouldPointTo  = $dice->Value + $fromPointNo;
                $toPoint = $this->Points->filter(
                    function( $entry ) use ( $currentPlayer, $shouldPointTo ) {
                        return $entry->GetNumber( $currentPlayer ) == $shouldPointTo;
                    }
                )->first();
                    
                // no creation of bearing off moves here. See next block.
                $canMove = $moves->exists(
                    function( $entry ) use ( $fromPoint, $toPoint ) {
                        return $entry->From == $fromPoint && $entry->To == $toPoint;
                    }
                );
                
                if (
                    $toPoint != null &&
                    $toPoint->IsOpen( $currentPlayer ) &&
                    ! $canMove &&
                    ! $toPoint->IsHome( $currentPlayer )
                ) {
                    $move = new Move();
                    $move->Color = $currentPlayer;
                    $move->From = $fromPoint;
                    $move->To = $toPoint;
                    
                    $moves[]    = $move;
                    $hit = $this->MakeMove( $move );
                    $this->_GenerateMoves( $move->NextMoves );
                    $this->UndoMove( $move, $hit );
                }
                    
                if ( $this->IsBearingOff( $currentPlayer ) ) {
                    // The furthest away checker can be moved beyond home
                    $minPoint = $this->calcMinPoint( $currentPlayer );
                    $toPointNo = $fromPointNo == $minPoint ? \min( 25, $fromPointNo + $dice->Value ) : $fromPointNo + $dice->Value;
                    $toPoint = $this->Points->filter(
                        function( $entry ) use ( $currentPlayer, $toPointNo ) {
                            return $entry->GetNumber( $currentPlayer ) == $toPointNo;
                        }
                    )->first();
                    
                    $canMove = ! $moves->filter(
                        function( $entry ) use ( $fromPoint, $toPoint ) {
                            return $entry->From == $fromPoint && $entry->To == $toPoint;
                        }
                    )->isEmpty();
                    
                    if (
                        $toPoint != null &&
                        $toPoint->IsOpen( $currentPlayer ) &&
                        ! $canMove
                    ) {
                        $move = new Move();
                        $move->Color = $this->CurrentPlayer;
                        $move->From = $fromPoint;
                        $move->To = $toPoint;
                        
                        $moves[]    = $move;
                        $hit = $this->MakeMove( $move );
                        $this->_GenerateMoves( $move->NextMoves );
                        $this->UndoMove( $move, $hit );
                    }
                }
            }
            
            $dice->Used = false;
        }
    }
    
    public function MakeMove( Move $move ): Checker
    {
        $checker = $move->From->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry->Color == $move->Color;
            }
        )->first();
        
        if ( $checker == null ) {
            throw new \RuntimeException( "There should be a checker on this point. Something is very wrong" );
        }
        
        $move->From->Checkers->removeElement( $checker );
        $move->To->Checkers[]   = $checker;
        if ( $move->Color == PlayerColor::Black ) {
            $this->BlackPlayer->PointsLeft -= ( $move->To->BlackNumber - $move->From->BlackNumber);
        } else {
            $this->WhitePlayer->PointsLeft -= ( $move->To->WhiteNumber - $move->From->WhiteNumber );
        }
            
        // Feels wrong that now that own home is same point as opponent bar
        // Todo: Try to change it some day
        $hit = $move->To->IsHome( $move->Color ) ? null : $move->To->Checkers->filter(
            function( $entry ) use ( $checker ) {
                return $entry->Color == $checker->Color;
            }
        )->first();
        if ( $hit != null ) {
            $move->To->Checkers->removeElement( $hit );
            $bar = $this->Points->filter(
                function( $entry ) {
                    return $entry->GetNumber( $this->OtherPlayer() ) == 0;
                }
            )->first();
                
            $bar->Checkers[]    = $hit;
            if ( $move->Color == PlayerColor::Black ) {
                $this->WhitePlayer->PointsLeft += ( 25 - $move->To->WhiteNumber );
            } else {
                $this->BlackPlayer->PointsLeft += ( 25 - $move->To->BlackNumber );
            }
        }
        
        return $hit;
    }
    
    public function UndoMove( Move $move, Checker $hitChecker ): void
    {
        $checker = $move->To->Checkers->filter(
            function( $entry ) use ( $move ) {
                return $entry->Color == $move->Color;
            }
        )->first();
        
        $move->To->Checkers->removeElement( $checker );
        $move->From->Checkers[] = $checker;
        if ( $move->Color == PlayerColor::Black ) {
            $this->BlackPlayer->PointsLeft += ( $move->To->BlackNumber - $move->From->BlackNumber );
        } else {
            $this->WhitePlayer->PointsLeft += ( $move->To->WhiteNumber - $move->From->WhiteNumber );
        }
            
        if ( $hitChecker != null ) {
            $move->To->Checkers[]   = $hitChecker;
            $bar = $this->Points->filter(
                function( $entry ) {
                    return $entry->GetNumber( $this->OtherPlayer() ) == 0;
                }
            )->first();
            $bar->Checkers->removeElement( $hitChecker );
            if ( $move->Color == PlayerColor::Black ) {
                $this->WhitePlayer->PointsLeft -= ( 25 - $move->To->WhiteNumber );
            } else {
                $this->BlackPlayer->PointsLeft -= ( 25 - $move->To->BlackNumber );
            }
        }
    }
    
    public function getRollOrderByDescending(): Collection
    {
        $dicesIterator  = $this->Roll->getIterator();
        $dicesIterator->uasort( function ( $a, $b ) {
            return $a->Value > $b->Value;
        });
            
        return new ArrayCollection( \iterator_to_array( $dicesIterator ) );
    }
    
    public function getMovesOrderByDescending( Collection $moves ): Collection
    {
        $movesIterator  = $moves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) {
            return $a->Value > $b->Value;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    public function getPointsOrdered( $currentPlayer ): Collection
    {
        $points = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->Checkers->filter(
                    function( $entry ) use ( $currentPlayer ) {
                        return $entry->Color == $currentPlayer;
                    }
                );
            }
        );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $a->GetNumber( $currentPlayer ) < $b->GetNumber( $currentPlayer );
        });
            
        return new ArrayCollection( \iterator_to_array( $pointsIterator ) );
    }
    
    public function calcMinPoint( $currentPlayer )
    {
        $points = $this->Points->filter(
            function( $entry ) use ( $currentPlayer ) {
                return $entry->Checkers->filter(
                    function( $entry ) use ( $currentPlayer ) {
                        return $entry->Color == $currentPlayer;
                    }
                );
            }
        );
        
        $pointsIterator  = $points->getIterator();
        $pointsIterator->uasort( function ( $a, $b ) use ( $currentPlayer ) {
            return $a->GetNumber( $currentPlayer ) < $b->GetNumber( $currentPlayer );
        });
            
        return ( new ArrayCollection( \iterator_to_array( $pointsIterator ) ) )->first()->GetNumber( $currentPlayer );
    }
    
    public function ReallyStarted(): bool
    {
        return $this->BlackPlayer->FirstMoveMade && $this->WhitePlayer->FirstMoveMade;
    }
}
