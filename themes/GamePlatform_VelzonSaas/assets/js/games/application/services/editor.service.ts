import { HttpClient } from '@angular/common/http';
const { context } = require( '../context' );

import { Injectable, Inject } from '@angular/core';
import { map, take } from 'rxjs/operators';

// Board Interfaces
import CheckerDto from '_@/GamePlatform/Model/BoardGame/checkerDto';
import DiceDto from '_@/GamePlatform/Model/BoardGame/diceDto';
import GameDto from '_@/GamePlatform/Model/BoardGame/gameDto';
import MoveDto from '_@/GamePlatform/Model/BoardGame/moveDto';
import PlayerColor from '_@/GamePlatform/Model/BoardGame/playerColor';
import { GameStringRequest } from '../dto/editor/gameStringRequest';
import { GameStringResponseDto } from '../dto/editor/gameStringResponseDto';

import { AppStateService } from '../state/app-state.service';

@Injectable({
    providedIn: 'root'
})
export class EditorService
{
    url: string;

    constructor(
        @Inject( HttpClient ) private httpClient: HttpClient,
        @Inject( AppStateService ) private appState: AppStateService
    ) {
        this.url    = `${context.apiURL}`;
    }
    
    doMove( move: MoveDto )
    {
        const prevGame = this.appState.game.getValue();
        const gameClone = JSON.parse( JSON.stringify( prevGame ) ) as GameDto;
        const from = move.from;
        const to = move.to;
        
        // remove moved checker
        const checker = <CheckerDto>(
            gameClone.points[from].checkers.find( ( c ) => c.color === move.color )
        );
        const index = gameClone.points[from].checkers.indexOf( checker );
        gameClone.points[from].checkers.splice( index, 1 );
        
        if ( move.color == PlayerColor.black ) {
            gameClone.blackPlayer.pointsLeft -= move.to - move.from;
        } else {
            gameClone.whitePlayer.pointsLeft -= move.from - move.to;
        }
        
        //push checker to new point
        gameClone.points[to].checkers.push( checker );
        
        this.appState.game.setValue( gameClone );
    }
    
    setStartPosition(): void
    {
        const game: GameDto = {
            id: '15f126cb-e84d-4fe7-9782-8767109eed49',
            blackPlayer: {
                name: 'Guest',
                playerColor: 0,
                pointsLeft: 167,
                photoUrl: '',
                elo: 1200,
                gold: 0,
                isAi: false
            },
            whitePlayer: {
                name: 'Guest',
                playerColor: 1,
                pointsLeft: 167,
                photoUrl: '',
                elo: 1383,
                gold: 0,
                isAi: false
            },
            currentPlayer: 0,
            winner: 2,
            playState: 0,
            points: [
                {
                    blackNumber: 0,
                    checkers: [],
                    whiteNumber: 25
                },
                {
                    blackNumber: 1,
                    checkers: [
                        {
                            color: 0
                        },
                        {
                            color: 0
                        }
                    ],
                    whiteNumber: 24
                },
                {
                    blackNumber: 2,
                    checkers: [],
                    whiteNumber: 23
                },
                {
                    blackNumber: 3,
                    checkers: [],
                    whiteNumber: 22
                },
                {
                    blackNumber: 4,
                    checkers: [],
                    whiteNumber: 21
                },
                {
                    blackNumber: 5,
                    checkers: [],
                    whiteNumber: 20
                },
                {
                    blackNumber: 6,
                    checkers: [
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        }
                    ],
                    whiteNumber: 19
                },
                {
                    blackNumber: 7,
                    checkers: [],
                    whiteNumber: 18
                },
                {
                    blackNumber: 8,
                    checkers: [
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        }
                    ],
                    whiteNumber: 17
                },
                {
                    blackNumber: 9,
                    checkers: [],
                    whiteNumber: 16
                },
                {
                    blackNumber: 10,
                    checkers: [],
                    whiteNumber: 15
                },
                {
                    blackNumber: 11,
                    checkers: [],
                    whiteNumber: 14
                },
                {
                    blackNumber: 12,
                    checkers: [
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        }
                    ],
                    whiteNumber: 13
                },
                {
                    blackNumber: 13,
                    checkers: [
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        },
                        {
                            color: 1
                        }
                    ],
                    whiteNumber: 12
                },
                {
                    blackNumber: 14,
                    checkers: [],
                    whiteNumber: 11
                },
                {
                    blackNumber: 15,
                    checkers: [],
                    whiteNumber: 10
                },
                {
                    blackNumber: 16,
                    checkers: [],
                    whiteNumber: 9
                },
                {
                    blackNumber: 17,
                    checkers: [
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        }
                    ],
                    whiteNumber: 8
                },
                {
                    blackNumber: 18,
                    checkers: [],
                    whiteNumber: 7
                },
                {
                    blackNumber: 19,
                    checkers: [
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        },
                        {
                            color: 0
                        }
                    ],
                    whiteNumber: 6
                },
                {
                    blackNumber: 20,
                    checkers: [],
                    whiteNumber: 5
                },
                {
                    blackNumber: 21,
                    checkers: [],
                    whiteNumber: 4
                },
                {
                    blackNumber: 22,
                    checkers: [],
                    whiteNumber: 3
                },
                {
                    blackNumber: 23,
                    checkers: [],
                    whiteNumber: 2
                },
                {
                    blackNumber: 24,
                    checkers: [
                        {
                            color: 1
                        },
                        {
                            color: 1
                        }
                    ],
                    whiteNumber: 1
                },
                {
                    blackNumber: 25,
                    checkers: [],
                    whiteNumber: 0
                }
            ],
            validMoves: [],
            thinkTime: 39.9999889,
            goldMultiplier: 1,
            isGoldGame: false,
            stake: 0
        };
        
        setTimeout( () => {
            this.appState.game.setValue( game );
            this.updateGameString();
        }, 1 );
    }
    
    updateGameString(): void
    {
        var url = `${this.url}/backgamon/editor/gamestring`;
        
        const dice: DiceDto[] = [
            { value: 5, used: false },
            { value: 6, used: false }
        ];
        const dto: GameStringRequest = {
            game: this.appState.game.getValue(),
            dice: dice
        };
        
        this.httpClient
            .post( url, dto )
            .pipe(
                take( 1 ),
                map( ( dto ) => dto as GameStringResponseDto )
            )
            .subscribe( ( data ) => {
                this.appState.gameString.setValue( data.value );
            });
    }
}
