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

	public function list($id)
	{
		return [[
			[
				'text' => 'Completed',
				'callback_data' => 'completedList__' . $id[0]
			]
//			, [
//				'text' => 'Edit',
//				'callback_data' => 'editList__' . $id[0]
//			]
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
