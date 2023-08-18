<?php

namespace App\Models;

class Buttons
{
    /*
    *******************
    *     BUTTONS     *
    *******************
    */

    public function start()
    {
        return [[
            'start'
        ]];
    }

    /*
    *******************
    * INLINE BUTTONS  *
    *******************
     */

    public function example()
    {
        return [[
            [
                'text' => '{edit}',
                'callback_data' => 'editDebtsToMe__'
            ], [
                'text' => '{delete}',
                'callback_data' => 'deleteDebtsToMe__'
            ]
        ]];
    }

    /*
    *******************
    *     DEFAULT     *
    *******************
    */

    public static function default(): array
    {
        return [];
    }
}
