<?php namespace App\Component;

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
}
