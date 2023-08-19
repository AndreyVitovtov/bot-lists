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
				'text' => '✔️ Mark',
				'callback_data' => 'completedList__' . $id[0]
			]
			, [
				'text' => '✏️ Edit',
				'callback_data' => 'editList__' . $id[0]
			]
		]];
	}

	public function listEdit($id)
	{
		return [[
			[
				'text' => '➕ Add',
				'callback_data' => 'addItem__' . $id[0]
			], [
				'text' => '🗑️ Delete',
				'callback_data' => 'deleteItems__' . $id[0]
			]
		],
			[
				[
					'text' => '🔙',
					'callback_data' => 'backList__' . $id[0]
				]
			]
		];
	}

	public function ok($id)
	{
		return [[[
			'text' => '👌',
			'callback_data' => 'completedItemsSave__' . $id[0]
		]]];
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
