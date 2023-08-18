<?php

namespace App\Controllers;

use App\Models\InlineButtons;
use App\Models\Lists;

trait MethodsFromGroupAndChat
{
	public function methodFromGroupAndChat()
	{
		$type = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $this->getType()))));
		if (method_exists($this, $type)) {
			$this->$type();
		} else {
			$this->groupAndChatUnknownTeam();
		}
	}

	public function newChatParticipant()
	{
		$data = $this->getDataByType();
	}

	public function leftChatParticipant()
	{
		$data = $this->getDataByType();
	}

	public function callbackQuery()
	{
		$message = explode('__', $this->getMessage());

		$command = $message[0];
		$id = $message[1];

		$list = new Lists();

		switch ($command) {
			case 'completedList':
				$this->deleteMessage($this->getMessageId());
				$title = $list->getList($id)['title'];
				$this->send("<b>" . $title . "</b>\n\n", $this->buttonsListItems($id, 'completedItem', [
					'text' => '👌 Ok',
					'callback_data' => 'completedItemsSave__' . $id
				]));
				break;
			case 'completedItem':
				$this->sendTyping();
				$list->completed($id);
				$listInfo = $list->getListByItemId($id);
				$res = $this->telegram->editMessageText(
					$this->chat,
					$this->getMessageId(),
					"<b>" . $listInfo['title'] . "</b>",
					$this->buttonsListItems($listInfo['id'], 'completedItem', [
						'text' => '👌 Ok',
						'callback_data' => 'completedItemsSave__' . $listInfo['id']
					], true)
				);
				echo $res;
				break;
			case 'completedItemsSave':
				$this->deleteMessage($this->getMessageId());
				$this->send($this->stringListItems($id), InlineButtons::list($id));

				break;
			case 'editList':
				$this->send('In development');
				break;
		}
	}

	public function groupAndChatUnknownTeam()
	{
		if (substr($this->getMessage(), 0, 10) == 'createlist') {
			$this->createList();
		}
//		else {
//			$this->unknownTeam();
//		}
	}

	private function createList()
	{
		preg_match_all('/createlist\s\((.+)\):(.+)/', $this->getMessage(), $matches);

		$listTitle = trim($matches[1][0] ?? 'No title');
		$items = $matches[2][0];

		$items = array_map(function ($v) {
			return ucfirst(trim($v));
		}, explode(',', $items));

		$items = array_filter($items, function($v) {
			return !empty($v);
		});

		$items = array_unique($items);

		$list = new Lists();
		$listId = $list->addList($this->chat, $listTitle);

		foreach ($items as $item) {
			$list->addItem($listId, $item);
		}

		$this->deleteIncomingMessage();

		$this->send($this->stringListItems($listId), InlineButtons::list($listId));
	}

	private function stringListItems($listId)
	{
		$list = new Lists();

		$listTitle = $list->getList($listId)['title'];

		$items = $list->getItems($listId);

		$listItems = array_map(function ($k) use ($items) {
			return $k + 1 . '. ' . ($items[$k]['completed'] ? "<s>" . $items[$k]['title'] . "</s>" : $items[$k]['title']);
		}, array_keys($items));

		return "<b>" . $listTitle . "</b>\n\n" . implode("\n", $listItems);
	}

	private function buttonsListItems($listId, $callbackQuery, $lastButtons, $edit = false)
	{
		$list = new Lists();

		$items = $list->getItems($listId);
		$items = array_map(function ($v) {
			$v['title'] = ($v['completed'] ? "☑️ " : "⬜ ") . $v['title'];
			return $v;
		}, $items);

		return InlineButtons::custom(
			$items,
			1,
			$callbackQuery,
			'title',
			null,
			'id',
			$lastButtons,
			$edit
		);
	}
}
