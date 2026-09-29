<?php namespace App\Component;

use App\Component\Type\PlayerColor;
use App\Component\Type\PlayerPosition;

final class GamePlatform
{
    const GAME_STATUS_NOT_IMPLEMENTED       = 'not_implemented';
    const GAME_STATUS_IN_DEVELOPEMENT       = 'in_developement';
    const GAME_STATUS_IN_DEVELOPEMENT_BUT   = 'in_developement_but';
    const GAME_STATUS_DONE                  = 'game_is_done';
    
    const GAME_STATUS   = [
        self::GAME_STATUS_NOT_IMPLEMENTED       => 'game_platform.form.game.not_implemented',
        self::GAME_STATUS_IN_DEVELOPEMENT       => 'game_platform.form.game.in_developement',
        self::GAME_STATUS_IN_DEVELOPEMENT_BUT   => 'game_platform.form.game.in_developement_but',
        self::GAME_STATUS_DONE                  => 'game_platform.form.game.game_is_done',
    ];
    
    const GAME_TYPE_BOARD_GAME          = 'board_game';
    const GAME_TYPE_CARD_GAME           = 'card_game';
    const GAME_TYPE_CARD_GAME_NO_TEAMS  = 'card_game_no_teams';
    
    const GAME_TYPE   = [
        self::GAME_TYPE_BOARD_GAME          => 'game_platform.form.game.board_game',
        self::GAME_TYPE_CARD_GAME           => 'game_platform.form.game.card_game',
        self::GAME_TYPE_CARD_GAME_NO_TEAMS  => 'game_platform.form.game.card_game_no_teams',
    ];
    
    const GAME_PLAYER_COLOR_BLACK   = 0;
    const GAME_PLAYER_COLOR_WHITE   = 1;
    
    const GAME_PLAYER_COLOR   = [
        self::GAME_PLAYER_COLOR_BLACK   => 'game_platform.form.game_player_color_black',
        self::GAME_PLAYER_COLOR_WHITE   => 'game_platform.form.game_player_color_white',
    ];
    
    const GAME_PLAYER_POSITION_SOUTH  = 0;
    const GAME_PLAYER_POSITION_EAST   = 1;
    const GAME_PLAYER_POSITION_NORTH  = 2;
    const GAME_PLAYER_POSITION_WEST   = 3;
    
    const GAME_PLAYER_POSITION   = [
        self::GAME_PLAYER_POSITION_SOUTH  => 'game_platform.form.game_player_position_south',
        self::GAME_PLAYER_POSITION_EAST   => 'game_platform.form.game_player_position_east',
        self::GAME_PLAYER_POSITION_NORTH  => 'game_platform.form.game_player_position_north',
        self::GAME_PLAYER_POSITION_WEST   => 'game_platform.form.game_player_position_west',
    ];
    
    const GAME_ROOM_STATUS_WAITING  = 'waiting';
    const GAME_ROOM_STATUS_PLAYING  = 'playing';
    const GAME_ROOM_STATUS_FULL     = 'full';
}
