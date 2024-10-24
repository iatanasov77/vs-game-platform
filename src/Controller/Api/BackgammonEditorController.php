<?php namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\Common\Collections\Collection;
use App\Component\Dto\editor\GameStringRequest;
use App\Component\Dto\editor\GameStringResponseDto;
use App\Component\Dto\GameDto;
use App\Component\Dto\DiceDto;
use App\Component\Type\PlayerColor;

class BackgammonEditorController extends AbstractController
{
    public function gameString( Request $request ): Response
    {
        /** @var GameStringRequest */
        $g          = \json_decode( $request->getContent() );
        $value      = $this->getGameString( $g->game, $g->dice );
        
        $dto        = new GameStringResponseDto();
        $dto->value = $value;
        
        return new JsonResponse( $dto );
    }
    
    private function getGameString( GameDto $g, Collection $dice ): string
    {
        $s = "board ";
        
        $blackBar = $g->points[0]->checkers->filter(
            function( $entry ) {
                return $entry->color == PlayerColor::Black;
            }
        )->count();
        $s  .= "b{$blackBar} ";
        
        for ( $i = 1; $i < 25; $i++ ) {
            $checkers = $g->points[$i]->checkers;
            if ( $checkers->count() > 0 ) {
                $color = $checkers[0]->color;
                if ( $color == PlayerColor::Black ) {
                    $s  .= 'b';
                } else {
                    $s  .= 'w';
                }
            }
            $s  .= $checkers->count() . " ";
        }
        
        $whiteBar = $g->points[25]->checkers->filter(
            function( $entry ) {
                return $entry->color == PlayerColor::White;
            }
        )->count();
        $s  .= "w{$whiteBar} ";
        
        $whiteHome = $g->points[0]->checkers->checkers->filter(
            function( $entry ) {
                return $entry->color == PlayerColor::White;
            }
        )->count();
        $s  .= "{$whiteHome} ";
        
        $blackHome = $g->points[25]->checkers->filter(
            function( $entry ) {
                return $entry->color == PlayerColor::Black;
            }
        )->count();
        $s  .= "{$blackHome} ";
        
        $s  .= ( $g->currentPlayer == PlayerColor::Black ? "b " : "w " );
        $s  .= $dice[0]->value . " ";
        $s  .= $dice[1]->value;
        
        return $s;
    }
}
