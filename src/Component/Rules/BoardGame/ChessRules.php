<?php namespace App\Component\Rules\BoardGame;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Component\GameLogger;
use App\Component\Type\PlayerColor;
use App\Component\Type\ChessPieceType;
use App\Component\Type\ChessMoveType;

class ChessRules
{
    /** @var ChessGame */
    private $game;
    
    /** @var GameLogger */
    private  $logger;
    
    public function __construct( ChessGame $game, GameLogger $logger )
    {
        $this->game             = $game;
        $this->logger           = $logger;
    }
    
    // Return true if the given side is checkmate
    public function IsCheckMate( PlayerColor $PlayerSide ): bool
    {
        // if player is under check and he has no moves
        if ( $this->IsUnderCheck( $PlayerSide ) && $this->GetCountOfPossibleMoves( $PlayerSide ) == 0 ) {
            return true;	// player is checkmate
        } else {
            return false;
        }
    }
    
    // Return true if the given side is stalemate
    public function IsStaleMate( PlayerColor $PlayerSide ): bool
    {
        // if player is not under check and he has no moves
        if ( ! $this->IsUnderCheck( $PlayerSide ) && $this->GetCountOfPossibleMoves( $PlayerSide ) == 0 ) {
            return true;	// player is checkmate
        } else {
            return false;
        }
    }
    
    // Returns all the possible moves wether they are legal or not i.e. some moves may cause or leave check
    // so for the particular situation they may become illegal
    public function GetPossibleMoves( ChessSquare $source ): Collection
    {
        $LegalMoves = new ArrayCollection();
        
        if ( ! $source->Piece ) {
            return $LegalMoves;
        }
        
        // Check the legal moves for the object
        switch ( $source->Piece->Type )
        {
            case ChessPieceType::Pawn:	// Pawn object
                $this->GetPawnMoves( $source, $LegalMoves );
                break;
                
            case ChessPieceType::Knight:	// Knight object
                $this->GetKnightMoves( $source, $LegalMoves );
                break;
                
            case ChessPieceType::Rook:	// Rook piece
                $this->GetRookMoves( $source, $LegalMoves );
                break;
                
            case ChessPieceType::Bishop:	// Bishop piece
                $this->GetBishopMoves( $source, $LegalMoves );
                break;
                
            case ChessPieceType::Queen:	// Queen piece
                $this->GetQueenMoves( $source, $LegalMoves );
                break;
                
            case ChessPieceType::King:	// king piece
                $this->GetKingMoves( $source, $LegalMoves );
                break;
        }
        
        return $LegalMoves;
    }
    
    // Returns all the legal moves for the given cell
    public function GetLegalMoves( ChessSquare $source ): Collection
    {
        $LegalMoves = $this->GetPossibleMoves( $source );	// Get the legal moves
        $ToRemove = new ArrayCollection();	// contains a list of all the moves to remove
        
        // Now check and mark all the moves which moves user under check
        foreach ( $LegalMoves as $target ) {
            // if the move place or leave the user under check
            $move = new ChessMove();
            $move->From = $source;
            $move->To = $target;
            if ( $this->CauseCheck( $move ) ) {
                $ToRemove[] = $target;
            }
        }
        
        // When checking the moves for the king, don't allow tower/caslting, if
        // the king is under check
        if ( $source->Piece->Type == ChessPieceType::King && $this->IsUnderCheck( $source->Piece->Side->type ) ) {
            foreach ( $LegalMoves as $target ) {
                // if the move place or leave the user under check
                
                if ( \abs( \ord( $target->File ) - \ord( $source->File ) ) > 1 ) {
                    $ToRemove[] = $target;
                }
            }
        }
        
        // remove all the illegal moves
        foreach ( $ToRemove as $cell ) {
            $LegalMoves->removeElement( $cell );	// remove the illegal move
        }
        
        return $LegalMoves;
    }
    
    // Generate all the legal moves for the given side and return back moves in the
    // sorted order
    public function GenerateAllLegalMoves( ChessSide $PlayerSide ): Collection
    {
        $TotalMoves = new ArrayCollection();
        $PlayerCells = $this->game->GetSideCell( $PlayerSide->type );
        
        // Loop all the owner squars and get their possible moves
        foreach ( $PlayerCells as $CellName ) {
            $moves = $this->GetLegalMoves( $this->game->Squares[$CellName] );	// Get all the legal moves for the owner piece
            
            foreach ( $moves as $dest ) {
                $move = new ChessMove();
                $move->From = $this->game->Squares[$CellName];
                $move->To = $dest;
                $this->SetMoveType( $move );				// Set the move type
                
                if ( $move->IsPromoMove() ) {			// Pawn promotion move
                    $move->Score = 1000;
                } else if ( $move->IsCaptureMove() ) {	// Pawn capture move
                    $move->Score = $move->To->Piece->GetWeight();	// Sort by item being captured
                }
                
                $TotalMoves[] = $move;	// Add the move to total moves
            }
        }
        
        // For the best performance of the alpha beta search. It's extemely important
        // that the best sort is tried first. So that cut of is achieved in early moves
        
        // Move the moves with highest score on top of the array
        $movesIterator  = $TotalMoves->getIterator();
        $movesIterator->uasort( function ( $a, $b ) {
            return $b->Score <=> $a->Score;
        });
            
        return new ArrayCollection( \iterator_to_array( $movesIterator ) );
    }
    
    // Return the last executed move
    public function GetLastMove(): ?ChessMove
    {
        // Check if there are Undo Moves available
        if ( $this->game->MovesHistory->count() > 0 ) {
            return $this->game->MovesHistory->last();	// Ge the user move from his moves history stack
        }
        
        return null;
    }
    
    // Returns the cell on the top of the given cell
    public function TopCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank - 1;
        $key = "{$cell->File}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the left of the given cell
    public function LeftCell( ChessSquare $cell ): ?ChessSquare
    {
        $newCol = \chr( \ord( $cell->File ) - 1 );
        $key = "{$newCol}{$cell->Rank}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the right of the given cell
    public function RightCell( ChessSquare $cell ): ?ChessSquare
    {
        $newCol = \chr( \ord( $cell->File ) + 1 );
        $key = "{$newCol}{$cell->Rank}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the bottom of the given cell
    public function BottomCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank + 1;
        $key = "{$cell->File}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the top-left of the current cell
    public function TopLeftCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank - 1;
        $newCol = \chr( \ord( $cell->File ) - 1 );
        $key = "{$newCol}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the top-right of the current cell
    public function TopRightCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank - 1;
        $newCol = \chr( \ord( $cell->File ) + 1 );
        $key = "{$newCol}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the bottom-left of the current cell
    public function BottomLeftCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank + 1;
        $newCol = \chr( \ord( $cell->File ) - 1 );
        $key = "{$newCol}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Returns the cell on the bottom-right of the current cell
    public function BottomRightCell( ChessSquare $cell ): ?ChessSquare
    {
        $newRow = $cell->Rank + 1;
        $newCol = \chr( \ord( $cell->File ) + 1 );
        $key = "{$newCol}{$newRow}";
        
        return $this->game->Squares->containsKey( $key ) ? $this->game->Squares[$key] : null;
    }
    
    // Actually execute the move
    public function ExecuteMove( ChessMove $move ): void
    {
        if ( ! $move->From || ! $this->game->Squares["{$move->From}"] ) {
            return;
        }
        
        //$this->logger->log( "MakeMove: " . print_r( $this->game->Squares["{$move->From}"], true ), 'GenerateMoves' );
        //$this->logger->log( "MakeMove: {$move->From}", 'GenerateMoves' );
        
        // Check and execute the the move
        switch ( $move->Type ) {
            case ChessMoveType::CaputreMove:	// Capture move
                $this->DoNormalMove( $move );
                break;
                
            case ChessMoveType::NormalMove:		// Normal move
                $this->DoNormalMove( $move );
                break;
                
            case ChessMoveType::TowerMove:		// Tower move
                $this->DoTowerMove( $move );
                break;
                
            case ChessMoveType::PromotionMove:	// Promotion move
                $this->DoPromoMove( $move );
                break;
                
            case ChessMoveType::EnPassant:		// EnPassant move
                $this->DoEnPassantMove( $move );
                break;
        }
    }
    
    // Undo the user move
    public function UndoMove( ChessMove $move ): void
    {
        if (
            $move->Type == ChessMoveType::CaputreMove ||
            $move->Type == ChessMoveType::NormalMove ||
            $move->Type == ChessMoveType::PromotionMove
        ) {
            $this->UndoNormalMove( $move );
        }
            
        // Undo the tower move
        if ( $move->Type == ChessMoveType::TowerMove ) {
            $this->UndoNormalMove( $move );	// First move the king to it's orignal position
            if ( \ord( $move->To->File ) > \ord( $move->From->File ) ) { // moving right
                // Now move the rook back to it's orignal position
                $source = $this->LeftCell( $move->To );	// Get the new position of the rock
                $key = "H{$move->From->Rank}";
                $target = $this->game->Squares[$key];	// Get the rook orignal position
                
                if ( $this->game->Squares["{$source}"]->Piece ) {
                    $this->game->Squares["{$source}"]->Piece->Moves--;	// decrement moves
                }
                $this->game->Squares["{$target}"]->Piece = $this->game->Squares["{$source}"]->Piece;		// Move object at the destination
                $this->game->Squares["{$source}"]->Piece = null;	// Empty the source location
            } else {	// Moving Left
                // Now move the rook back to it's orignal position
                $source = $this->RightCell( $move->To );	// Get the new position of the rock
                $key = "A{$move->From->Rank}";
                $target = $this->game->Squares[$key];	// Get the rook orignal position
                
                if ( $this->game->Squares["{$source}"]->Piece ) {
                    $this->game->Squares["{$source}"]->Piece->Moves--;	// decrement moves
                }
                $this->game->Squares["{$target}"]->Piece = $this->game->Squares["{$source}"]->Piece;		// Move object at the destination
                $this->game->Squares["{$source}"]->Piece = null;	// Empty the source location
            }
        }
        
        // Undo the EnPassant move
        if ( $move->Type == ChessMoveType::EnPassant ) {
            $this->UndoNormalMove( $move );
            if ( $move->From->Piece->Side->isWhite() ) {	// white piece was moved
                $EnPassantCell = $this->BottomCell( $move->To );	// Get the cell under target position
            } else {
                $EnPassantCell =$this->TopCell( $move->To );	// Get the cell under target position
            }
            
            $EnPassantCell->Piece = $move->EnPassantPiece;	// set back the enpassant piece
        }
    }
    
    // Return true if the given side type is under check state
    public function IsUnderCheck( PlayerColor $PlayerSide ): bool
    {
        $OwnerKingCell=null;
        $OwnerCells = $this->game->GetSideCell( $PlayerSide );
        
        // loop all the owner squars and get his king cell
        foreach ( $OwnerCells as $CellName ) {
            if ( $this->game->Squares[$CellName]->Piece->Type == ChessPieceType::King ) {
                $OwnerKingCell = $this->game->Squares[$CellName]; // store the enemy cell position
                break;	// break the loop
            }
        }
        
        // Loop all the enemy squars and get their possible moves
        $EnemyCells = $this->game->GetSideCell( ( new ChessSide( $PlayerSide ) )->Enemy() );
        foreach ( $EnemyCells as $CellName ) {
            $moves = $this->GetPossibleMoves( $this->game->Squares[$CellName] );	// Get the moves for the enemy piece
            // King is directly under attack
            if ( $moves->contains( $OwnerKingCell ) ) {
                return true;
            }
        }
        
        return false;
    }
    
    // Analyze the board and return back the evualted score for the given side
    public function AnalyzeBoard( PlayerColor $PlayerSide ): int
    {
        $Score = 0;
        $OwnerCells = $this->game->GetSideCell( $PlayerSide );
        
        // loop all the owner squars and get his king cell
        foreach ( $OwnerCells as $ChessCell ) {
            $Score += $this->game->Squares[$ChessCell]->Piece->GetWeight();
        }
        
        //int iPossibleMoves = GetCountOfPossibleMoves(PlayerSide);
        //Score+=iPossibleMoves*5; // Each mobility has 5 points
        return $Score;
    }
    
    // Evaulate the current board position and return the evaluation score
    public function Evaluate( ChessSide $PlayerSide ): int
    {
        $Score = 0;
        
        $Score = $this->AnalyzeBoard( $PlayerSide->type ) - $this->AnalyzeBoard( $PlayerSide->Enemy() ) - 25;
        
        if ( $this->IsCheckMate( $PlayerSide->Enemy() ) ) {	// If the player is check mate
            $Score = 1000000;
        }
            
        return $Score;
    }
    
    // return type of the move given the move object
    private function SetMoveType( ChessMove $move ): void
    {
        // start with the normal move type
        $move->Type = ChessMoveType::NormalMove;
        
        // check if the move is of capture type
        if ( $move->To->Piece && $move->To->Piece->Type ) {
            $move->Type = ChessMoveType::CaputreMove;
        }
        
        // check if the move is of tower/castling type
        if ( $move->From->Piece && $move->From->Piece->Type == ChessPieceType::King ) {
            if ( \abs( \ord( $move->To->File ) - \ord( $move->From->File ) ) > 1 ) { // king can move to other than neighbour cell only in tower move
                $move->Type = ChessMoveType::TowerMove;
            }
        }
        
        // check if the move is a pawn promotion move
        if ( $move->From->Piece && $move->From->Piece->Type == ChessPieceType::Pawn ) {
            // Pawn is being promoted
            if ( $move->To->Rank == 8 || $move->To->Rank == 1 ) {
                $move->Type = ChessMoveType::PromotionMove;
            }
        }
        
        // check if the move is a en passant move
        if ( $move->From->Piece && $move->From->Piece->Type == ChessPieceType::Pawn ) {
            // Pawn is being being moved in a corner without a piece
            if ( ! $move->To->Piece && $move->From->File != $move->To->File ) {
                $move->Type = ChessMoveType::EnPassant;
            }
        }
    }
    
    // Do the normal move i.e. desitnation is empty; simply move the source piece
    private function DoNormalMove( ChessMove $move ): void
    {
        if ( $this->game->Squares["{$move->From}"]->Piece && $this->game->Squares["{$move->From}"]->Piece->Moves !== null ) {
            $this->game->Squares["{$move->From}"]->Piece->Moves++;	// incremenet moves
        }
        
        $this->game->Squares["{$move->To}"]->Piece = $this->game->Squares["{$move->From}"]->Piece;		// Move object at the destination
        $this->game->Squares["{$move->From}"]->Piece = null;	// Empty the source location
    }
    
    // Do the castling/tower move. King interchanges it's position with it's rock
    private function DoTowerMove( ChessMove $move ): void
    {
        $this->DoNormalMove( $move );	// move the king to target position
        
        // Now check the direction of the king movement
        if ( \ord( $move->To->File ) > \ord( $move->From->File ) ) { // moving right
            $rockcell = $this->RightCell( $move->To );
            
            // create the move for rock
            $newmove = new ChessMove();
            $newmove->From = $rockcell;
            $newmove->To = $this->LeftCell( $move->To );
            
            $this->DoNormalMove( $newmove ); // Move the rock
        } else {
            // Move to the left side
            $rockcell = $this->LeftCell( $move->To );
            $rockcell = $this->LeftCell( $rockcell );
            
            // create the move for rock
            $newmove = new ChessMove();
            $newmove->From = $rockcell;
            $newmove->To = $this->RightCell( $move->To );
            
            $this->DoNormalMove( $newmove ); // Move the rock
        }
    }
    
    // Do the pawn promotion move
    private function DoPromoMove( ChessMove $move ): void
    {
        $this->DoNormalMove( $move );	// Do the normal move
        // check if promo piece is already selected by the user
        if ( $move->PromoPiece == null ) {
            $pieceSide = new ChessSide( $this->game->CurrentPlayer );
            $this->game->Squares["{$move->To}"]->Piece = new ChessPiece( ChessPieceType::Queen, $pieceSide );	// Set the end cell to queen
        } else {
            $this->game->Squares["{$move->To}"]->Piece = $move->PromoPiece;
        }
    }
    
    // Do the EnPassant Move
    private function DoEnPassantMove( ChessMove $move ): void
    {
        if ( $move->From->Piece->Side.isWhite() ) {	// white piece is moving
            $EnPassantCell = $this->BottomCell( $move->To );	// Get the cell under target position
        } else {
            $EnPassantCell = $this->TopCell( $move->To );	// Get the cell under target position
            
            $move->EnPassantPiece = $EnPassantCell->Piece;				// Save a reference to the en passant cell
            $EnPassantCell->Piece = null;	// Empty the en-passant cell
            $this->DoNormalMove( $move );	// Move the pawn to it's target position
        }
    }
    
    private function UndoNormalMove( ChessMove $move ): void
    {
        $this->game->Squares["{$move->To}"]->Piece = $move->CapturedPiece;		// Move object at the destination
        $this->game->Squares["{$move->From}"]->Piece = $move->Piece;	// Empty the source location
        
        if ( $this->game->Squares["{$move->From}"]->Piece && $this->game->Squares["{$move->From}"]->Piece->Moves !== null ) {
            $this->game->Squares["{$move->From}"]->Piece->Moves--;	// decrement moves
        }
    }
    
    // Returns true if the last move was a pawn begin move. It's used for En Passant move detection
    private function LastMoveWasPawnBegin(): ?ChessMove
    {
        // Now get user last move and see if it's a pawn move
        $lastmove = $this->GetLastMove();
        
        if ( $lastmove ) {	// last moe is not available
            if ( $lastmove->Piece->Type == ChessPieceType::Pawn && $lastmove->Piece->Moves == 1 ) {
                return $lastmove;
            }
        }
        
        return null;
    }
    
    // Returns a count of all the possilbe moves for given side
    private function GetCountOfPossibleMoves( PlayerColor $PlayerSide ): int
    {
        $TotalMoves = 0;
        
        // Loop all the owner squars and get their possible moves
        $PlayerCells = $this->game->GetSideCell( $PlayerSide );
        foreach ( $PlayerCells as $CellName ) {
            $moves = $this->GetLegalMoves( $this->game->Squares[$CellName] );	// Get all the legal moves for the owner piece
            $TotalMoves += $moves->count();
        }
        
        return $TotalMoves;
    }
    
    private function CauseCheck( ChessMove $move ): bool
    {
        if ( ! $move->From || ! $this->game->Squares["{$move->From}"] ) {
            return false;
        }
        
        $CauseCheck = false;
        $PlayerSide = $move->From->Piece->Side->type;
        
        // To check if a move cause check, we actually need to execute and check the result of that move
        $this->ExecuteMove( $move );
        $CauseCheck = $this->IsUnderCheck( $PlayerSide );
        $this->UndoMove( $move );	// undo the move
        
        return $CauseCheck;
    }
    
    // calculate the possible moves for the pawn object and insert them into passed array
    private function GetPawnMoves( ChessSquare $source, Collection &$moves ): void
    {
        if ( $source->Piece->Side->isWhite() ) {
            // Calculate moves for the white piece
            $newcell = $this->TopCell( $source );
            if ( $newcell && ! $newcell->Piece ) { // Top cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check the 2nd top element from source
            if ( $newcell && ! $newcell->Piece ) {
                $newcell = $this->TopCell( $newcell );
                if ( $newcell && $source->Piece->Moves == 0 && ! $newcell->Piece ) { // 2nd top cell is available and piece has not yet moved
                    $moves[] = $newcell;
                }
            }
            
            // Check top-left cell for enemy piece
            $newcell = $this->TopLeftCell( $source );
            if ( $newcell && $newcell->IsOwnedByEnemy( $source ) ) { // Top cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check top-right cell for enemy piece
            $newcell = $this->TopRightCell( $source );
            if ( $newcell && $newcell->IsOwnedByEnemy( $source ) ) { // Top cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check for possible En Passant Move
            $LastPawnMove= $this->LastMoveWasPawnBegin();
            
            if ( $LastPawnMove ) {	// last move was a pawn move
                if ( $source->Rank == $LastPawnMove->To->Rank ) { // Can do En Passant
                    if ( $LastPawnMove->To->File == \chr( \ord( $source->File ) - 1 ) ) {	// En Passant pawn is on left side
                        $newcell = $this->TopLeftCell( $source );
                        if ( $newcell && ! $newcell->Piece ) { // Top cell is available for the move
                            $moves[] = $newcell;
                        }
                    }
                    
                    if ( $LastPawnMove->To->File == \chr( \ord( $source->File ) + 1 ) ) {	// En Passant pawn is on left side
                        $newcell = $this->TopRightCell( $source );
                        if ( $newcell && ! $newcell->Piece ) { // Top cell is available for the move
                            $moves[] = $newcell;
                        }
                    }
                }
            }
        } else {
            // Calculate moves for the black piece
            $newcell = $this->BottomCell( $source );
            if ( $newcell && ! $newcell->Piece ) { // bottom cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check the 2nd bottom cell from source
            if ( $newcell && ! $newcell->Piece ) {
                $newcell = $this->BottomCell( $newcell );
                if ( $newcell && $source->Piece->Moves == 0 && ! $newcell->Piece ) { // 2nd bottom cell is available and piece has not yet moved
                    $moves[] = $newcell;
                }
            }
            
            // Check bottom-left cell for enemy piece
            $newcell = $this->BottomLeftCell( $source );
            if ( $newcell && $newcell->IsOwnedByEnemy( $source ) ) { // Bottom cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check bottom-right cell for enemy piece
            $newcell = $this->BottomRightCell( $source );
            if ( $newcell && $newcell->IsOwnedByEnemy( $source ) ) { // Bottom cell is available for the move
                $moves[] = $newcell;
            }
            
            // Check for possible En Passant Move
            $LastPawnMove= $this->LastMoveWasPawnBegin();
            
            if ( $LastPawnMove ) {	// last move was a pawn move
                if ( $source->Rank == $LastPawnMove->To->Rank ) { // Can do En Passant
                    if ( $LastPawnMove->To->File == \chr( \ord( $source->File ) - 1 ) ) {	// En Passant pawn is on left side
                        $newcell = $this->BottomLeftCell( $source );
                        if ( $newcell && ! $newcell->Piece ) { // Bottom cell is available for the move
                            $moves[] = $newcell;
                        }
                    }
                    
                    if ( $LastPawnMove->To->File == \chr( \ord( $source->File ) + 1 ) ) {	// En Passant pawn is on left side
                        $newcell = $this->BottomRightCell( $source );
                        if ( $newcell && ! $newcell->Piece ) { // Bottom cell is available for the move
                            $moves[] = $newcell;
                        }
                    }
                }
            }
        }
    }
    
    // calculate the possible moves for the knight piece and insert them into passed array
    private function GetKnightMoves( ChessSquare $source, Collection &$moves ): void
    {
        // First check top two left and right moves for knight
        $newcell = $this->TopCell( $source );
        if ( $newcell ) {
            $newcell = $this->TopLeftCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
            
            $newcell = $this->TopCell( $source );
            $newcell = $this->TopRightCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
        }
        // Now check 2nd bottom left and right cells
        $newcell = $this->BottomCell( $source );
        if ( $newcell ) {
            $newcell = $this->BottomLeftCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
            
            $newcell = $this->BottomCell( $source );
            $newcell = $this->BottomRightCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
        }
        // Now check 2nd Left Top and bottom cells
        $newcell = $this->LeftCell( $source );
        if ( $newcell ) {
            $newcell = $this->TopLeftCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
            
            $newcell = $this->LeftCell( $source );
            $newcell = $this->BottomLeftCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
        }
        // Now check 2nd Right Top and bottom cells
        $newcell = $this->RightCell( $source );
        if ( $newcell ) {
            $newcell = $this->TopRightCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
            
            $newcell = $this->RightCell( $source );
            $newcell = $this->BottomRightCell( $newcell );
            // target cell is empty or is owned by the enemy piece
            if ( $newcell && ! $newcell->IsOwned( $source ) ) {
                $moves[] = $newcell;
            }
        }
    }
    
    // calculate the possible moves for the Rook piece and insert them into passed array
    private function GetRookMoves( ChessSquare $source, Collection &$moves ): void
    {
        // Check all the move squars available in top direction
        $newcell = $this->TopCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->TopCell( $newcell ); // keep moving in the top direction
        }
        
        // Check all the move squars available in left direction
        $newcell = $this->LeftCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->LeftCell( $newcell ); // keep moving in the left direction
        }
        
        // Check all the move squars available in right direction
        $newcell = $this->RightCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->RightCell( $newcell ); // keep moving in the right direction
        }
        
        // Check all the move squars available in bottom direction
        $newcell = $this->BottomCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->BottomCell( $newcell ); // keep moving in the bottom direction
        }
    }
    
    // calculate the possible moves for the bishop piece and insert them into passed array
    private function GetBishopMoves( ChessSquare $source, Collection &$moves ): void
    {
        // Check all the move squars available in top-left direction
        $newcell = $this->TopLeftCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->TopLeftCell( $newcell ); // keep moving in the top-left direction
        }
        
        // Check all the move squars available in top-right direction
        $newcell = $this->TopRightCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->TopRightCell( $newcell ); // keep moving in the top-right direction
        }
        
        // Check all the move squars available in bottom-left direction
        $newcell = $this->BottomLeftCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->BottomLeftCell( $newcell ); // keep moving in the bottom-left direction
        }
        
        // Check all the move squars available in the bottom-right direction
        $newcell = $this->BottomRightCell( $source );
        while ( $newcell != null ) {	// move as long as cell is available in this direction
            if ( ! $newcell->Piece ) {	//next cell is available for move
                $moves[] = $newcell;
            }
            
            if ( $newcell->IsOwnedByEnemy( $source ) ) {	//next cell is owned by the enemy object
                $moves[] = $newcell;	// Add this to available location
                break;	// force quite the loop execution
            }
            
            if ( $newcell->IsOwned( $source ) ) {	//next cell contains owner object
                break;	// force quite the loop execution
            }
            
            $newcell = $this->BottomRightCell( $newcell ); // keep moving in the bottom-right direction
        }
    }
    
    // calculate the possible moves for the queen piece and insert them into passed array
    private function GetQueenMoves( ChessSquare $source, Collection &$moves ): void
    {
        // Queen has moves combination of both bishop and rook moves
        $this->GetRookMoves( $source, $moves ); // first get moves for the rook
        $this->GetBishopMoves( $source, $moves ); // then get moves for the bishop
    }
    
    // calculate the possible moves for the king piece and insert them into passed array
    private function GetKingMoves( ChessSquare $source, Collection &$moves ): void
    {
        // King can move to any of it's neighbor cells at the distance of one cell
        
        // check if king can move to top
        $newcell = $this->TopCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to left
        $newcell = $this->LeftCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to right
        $newcell = $this->RightCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to bottom
        $newcell = $this->BottomCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to top-left
        $newcell = $this->TopLeftCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to top-right
        $newcell = $this->TopRightCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to bottom-left
        $newcell = $this->BottomLeftCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        // check if king can move to bottom-right
        $newcell = $this->BottomRightCell( $source );
        if ( $newcell && ! $newcell->IsOwned( $source ) ) { // target cell is empty or is owned by the enemy piece
            $moves[] = $newcell;
        }
        
        // Check castling or tower moves for the king
        if ( $this->game->Squares["{$source}"]->Piece->Moves == 0 ) {
            $CastlingTarget = null;	// The cell where king will be moved in case of castling
            
            // As king has not yet moved, so castling is possible
            $newcell = $this->RightCell( $source );
            if ( $newcell && ! $newcell->Piece ) {	// cell is empty
                $checkMove = new ChessMove();
                $checkMove->From = $source;
                $checkMove->To = $newcell;
                if ( ! $this->CauseCheck( $checkMove ) ) { // Inbetween cell is not under check
                    $newcell = $this->RightCell( $newcell );
                    if ( $newcell && ! $newcell->Piece ) {	// cell is empty
                        $CastlingTarget = $newcell;	// This will be the king destination position
                        $newcell = $this->RightCell( $newcell );
                        if ( $newcell && $newcell->Piece && $newcell->Piece->Moves == 0 ) {	// check if the rook piece has not yet moved
                            $moves[] = $CastlingTarget;	// Add this as possible move
                        }
                    }
                }
            }
            
            // Check on the left side
            $newcell = $this->LeftCell( $source );
            if ( $newcell && ! $newcell->Piece ) {	// cell is empty
                $checkMove = new ChessMove();
                $checkMove->From = $source;
                $checkMove->To = $newcell;
                if ( ! $this->CauseCheck( $checkMove ) ) { // Inbetween cell is not under check
                    $newcell = $this->LeftCell( $newcell );
                    if ( $newcell && ! $newcell->Piece ) {	// cell is empty
                        $CastlingTarget = $newcell;	// This will be the king destination position
                        $newcell = $this->LeftCell( $newcell );
                        if ( $newcell && ! $newcell->Piece ) {	// cell is empty
                            $newcell = $this->LeftCell( $newcell );
                            if ( $newcell && $newcell->Piece && $newcell->Piece->Moves == 0 ) {	// check if the rook piece has not yet moved
                                $moves[] = $CastlingTarget; // Add this as possible move
                            }
                        }
                    }
                }
            }
        }
    }
}
