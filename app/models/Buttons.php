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
				'text' => TEXTS['mark'],
				'callback_data' => 'completedList__' . (is_array($id) ? $id[0] : $id)
			]
			, [
				'text' => TEXTS['edit'],
				'callback_data' => 'editList__' . (is_array($id) ? $id[0] : $id)
			]
		]];
	}

	public function listEdit($id)
	{
		return [[
			[
				'text' => TEXTS['add'],
				'callback_data' => 'addItem__' . (is_array($id) ? $id[0] : $id)
			], [
				'text' => TEXTS['delete'],
				'callback_data' => 'deleteItems__' . (is_array($id) ? $id[0] : $id)
			]
		],
			[
				[
					'text' => TEXTS['editTitleList'],
					'callback_data' => 'editTitleList__' . (is_array($id) ? $id[0] : $id)
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

	public function back($id)
	{
		return [[[
			'text' => '🔙',
			'callback_data' => 'backList__' . (is_array($id) ? $id[0] : $id)
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
