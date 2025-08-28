<?php namespace App\Component\AI\CardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Component\AI\AiEngineInterface;
use App\Component\GameLogger;
use App\Component\Type\PlayerColor;
use App\Component\Rules\CardGame\Game;
use App\Component\Rules\BoardGame\Helper as GameHelper;
use App\Component\Manager\AbstractGameManager;

abstract class Engine implements AiEngineInterface
{
    use GameHelper;
    
    /** @var GameLogger */
    protected $logger;
    
    /** @var Game */
    protected $EngineGame;
    
    /** @var (int dice1, int dice2)[] */
    protected $_allRolls = null;
    
    public function __construct( GameLogger $logger, Game $game )
    {
        $this->logger           = $logger;
        $this->EngineGame       = $game;
    }

    public function GetBestTricks(): Collection
    {
        $bestMoveSequence   = null;
        $bestScore          = - PHP_FLOAT_MAX;
        $allSequences       = $this->GenerateTricksSequence( $this->EngineGame );
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $allSequences->toArray(), true ), 'EngineGenerateMoves' );
        
        $oponent    = $this->EngineGame->OtherPlayer();
        $myColor    = $this->EngineGame->CurrentPlayer;
        
        foreach ( $allSequences as $sequence ) {
            $localSequence = $this->ToLocalSequence( $sequence, $this->EngineGame );
            
            $hits = $this->DoSequence( $localSequence, $this->EngineGame );
            $score = $this->EvaluatePoints( $myColor, $this->EngineGame ) + $this->EvaluateCheckers( $myColor, $this->EngineGame );
            
            $this->UndoSequence( $localSequence, $hits, $this->EngineGame );
            if ( $score > $bestScore ) {
                $bestScore = $score;
                $bestMoveSequence = $sequence;
            }
        }
        
        if ( $bestMoveSequence == null ) {
            $this->logger->debug( $allSequences->toArray(), 'EngineAllSequences.txt' );
            return new ArrayCollection();
        }
        
        //$this->logger->log( 'BestMoves Before Filter: ' . print_r( $bestMoveSequence->toArray(), true ), 'EnginMoves' );
        $bestMoveSequence   = $bestMoveSequence->filter(
            function( $entry ) {
                return $entry != null && ! $entry->isNull();
            }
        );
        
        if ( $myColor == PlayerColor::Black ) {
            return $this->getMovesOrderedByFromBlackNumber( $bestMoveSequence, AbstractGameManager::COLLECTION_ORDER_ASC );
        }
        
        try {
            return $this->getMovesOrderedByFromWhiteNumber( $bestMoveSequence, AbstractGameManager::COLLECTION_ORDER_ASC );
        } catch ( \Exception $e ) {
            $this->logger->log( print_r( $bestMoveSequence->toArray(), true ), 'EngineGenerateMoves' );
            throw $e;
        }
    }
    
    public function GenerateTricksSequence( Game $game ): Collection // List<Move[]>
    {
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $game->Roll->toArray(), true ), 'EngineGenerateMoves' );
        $sequences  = new ArrayCollection();
        $moves      = new ArrayCollection();
        foreach ( $game->Roll as $roll ) {
            $tricks[]    = new Move();
        }
        $sequences[]    = $tricks;
        
        $this->_GenerateTricksSequence( $sequences, $tricks, $game );
        //$this->logger->log( 'Engin GetBestMoves: ' . print_r( $sequences->toArray(), true ), 'EngineGenerateMoves' );
        
        return $sequences;
    }

    abstract protected function _GenerateTricksSequence( Collection &$sequences, Collection &$tricks, Game $game ): void;
    
    protected function ToLocalSequence( Collection $sequence, Game $game ): Collection
    {
        try {
            $moves = new ArrayCollection();
            for ( $i = 0; $i < count( $sequence ); $i++ ) {
                if ( ! $sequence[$i]->isNull() ) {
                    $move   = new Move();
                    $move->From = $game->Points[$sequence[$i]->From->BlackNumber];
                    $move->To = $game->Points[$sequence[$i]->To->BlackNumber];
                    $move->Color = $sequence[$i]->Color;
                        
                    $moves[] = $move;
                }
            }
        } catch ( \Exception $e ) {
            $this->logger->log( 'Exception at Engine::ToLocalSequence', 'EngineGenerateMoves' );
            $this->logger->log( 'Wrong Sequence: ' . print_r( $sequence->toArray(), true ), 'EngineGenerateMoves' );
            throw $e;
        }
        
        return $moves->count() ? $moves : $sequence;
    }

    protected function DoSequence( Collection $sequence, Game $game ): Collection
    {
        $hits = new ArrayCollection();
        foreach ( $sequence as $move ) {
            if ( $move == null || $move->isNull() ) {
                continue;
            }
            $hit = $game->MakeMove( $move );
            $hits[] = $hit;
        }
        $game->SwitchPlayer();
        
        return $hits;
    }

    protected function UndoSequence( Collection $sequence, Collection $hits, Game $game ): void
    {
        $game->SwitchPlayer();

        for ( $i = $sequence->count() - 1; $i >= 0; $i-- ) {
            if ( $sequence[$i] != null && $hits->count() ) {
                $lastHit    = $hits->last();
                $hits->removeElement( $lastHit );
                
                $game->UndoMove( $sequence[$i], $lastHit );
            }
        }
    }

    //Get the average score for current player rolling all possible combinations
    protected function ProbabilityScore( PlayerColor $myColor, Game $game ): float
    {
        $allDiceRoll = $this->AllRolls();
        $scores = [];
        $oponent = $myColor == PlayerColor::Black ? PlayerColor::White : PlayerColor::Black;
        foreach ( $allDiceRoll as $roll ) {
            $game->FakeRoll( $roll['dice1'], $roll['dice2'] );
            $bestScore = - PHP_FLOAT_MAX;
            $seqs = $this->GenerateMovesSequence( $game );
            foreach ( $seqs as $s ) {
                $hits = $this->DoSequence( $s, $game );
                $score = $this->EvaluatePoints( $myColor, $game ) + $this->EvaluateCheckers( $myColor, $game );
                $score -= $this->EvaluateCheckers( $oponent, $game );
                if ( $score > $bestScore ) {
                    $bestScore = $score;
                }
                $this->UndoSequence( $s, $hits, $game );
            }
            $m = $roll['dice1'] == $roll['dice2'] ? 1 : 2; // dice roll with not same value on dices are twice as probable. 2 / 36, vs 1 / 36
            if ( ! $seqs->isEmpty() ) {
                $scores[]   = $bestScore * $m;
            }
            // Get best score of each roll, and make an average
            // some rolls are more probable, multiply them
            // some rolls will be blocked or partially blocked
        }
        if ( ! \count( $scores ) ) {
            return -100000; // If player cant move, shes blocked. Thats bad.
        }
        
        return \array_sum( $scores ) / \count( $scores );
    }

    protected function Evaluate( PlayerColor $color, Game $game ): float
    {
        $score = $this->EvaluatePoints( $color, $game ) + $this->EvaluateCheckers( $color, $game );
        return $score;
    }
}
