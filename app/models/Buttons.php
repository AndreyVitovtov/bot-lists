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
				'callback_data' => 'completedList__' . (is_array($id) ? $id[0] : $id)
			]
			, [
				'text' => '✏️ Edit',
				'callback_data' => 'editList__' . (is_array($id) ? $id[0] : $id)
			]
		]];
	}

	public function listEdit($id)
	{
		return [[
			[
				'text' => '➕ Add',
				'callback_data' => 'addItem__' . (is_array($id) ? $id[0] : $id)
			], [
				'text' => '🗑️ Delete',
				'callback_data' => 'deleteItems__' . (is_array($id) ? $id[0] : $id)
			]
		],
			[
				[
					'text' => '🔙',
					'callback_data' => 'backList__' . (is_array($id) ? $id[0] : $id)
				]
			]
		];
	}

	public function ok($id)
	{
		return [[[
			'text' => '👌',
			'callback_data' => 'completedItemsSave__' . (is_array($id) ? $id[0] : $id)
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
