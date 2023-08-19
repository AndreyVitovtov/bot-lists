<?php

namespace App\Controllers;

use App\Models\Buttons;
use App\Models\InlineButtons;
use App\Models\Interaction;
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

		$command = trim($message[0]);
		$id = trim($message[1]);

		$list = new Lists();
		switch ($command) {
			case 'completedList':
				$title = $list->getList($id)['title'];
				$this->telegram->editMessageText(
					$this->chat, $this->getMessageId(),
					"<b>" . $title . "</b>\n\n",
					$this->buttonsListItems($id, 'completedItem', [
						'text' => '👌',
						'callback_data' => 'completedItemsSave__' . $id
					], true)
				);
				break;
			case 'completedItem':
				$list->completed($id);
				$listInfo = $list->getListByItemId($id);
				$this->telegram->editMessageText(
					$this->chat,
					$this->getMessageId(),
					"<b>" . $listInfo['title'] . "</b>",
					$this->buttonsListItems($listInfo['id'], 'completedItem', [
						'text' => '👌',
						'callback_data' => 'completedItemsSave__' . $listInfo['id']
					], true)
				);
				break;
			case 'backList':
			case 'completedItemsSave':
				Interaction::delete($this->chat);
				$buttons = new Buttons();
				$this->telegram->editMessageText(
					$this->chat,
					$this->getMessageId(),
					$this->stringListItems($id),
					$buttons->list($id)
				);
				break;
			case 'editList':
				$buttons = new Buttons();
				$this->telegram->editMessageText(
					$this->chat, $this->getMessageId(),
					$this->stringListItems($id),
					$buttons->listEdit($id)
				);
				break;
			case 'addItem':
				Interaction::set($this->chat, 'addItem', json_encode([
					'messageId' => $this->getMessageId(),
					'listId' => $id
				]));
				$buttons = new Buttons();
				$this->telegram->editMessageText(
					$this->chat, $this->getMessageId(),
					$this->stringListItems($id),
					$buttons->ok($id)
				);
				break;
			case 'deleteItems':
				$listInfo = $list->getList($id);
				$this->telegram->editMessageText(
					$this->chat,
					$this->getMessageId(),
					"<b>" . $listInfo['title'] . "</b>",
					$this->buttonsListItems($id, 'deleteItem', [
						'text' => '👌',
						'callback_data' => 'completedItemsSave__' . $id
					], true)
				);
				break;
			case 'deleteItem':
				$listInfo = $list->getListByItemId($id);
				$list->deleteItem($id);
				$this->telegram->editMessageText(
					$this->chat,
					$this->getMessageId(),
					"<b>" . $listInfo['title'] . "</b>",
					$this->buttonsListItems($listInfo['id'], 'deleteItem', [
						'text' => '👌',
						'callback_data' => 'completedItemsSave__' . $listInfo['id']
					], true)
				);
				break;
		}
	}

	public function groupAndChatUnknownTeam()
	{
		if (strtolower(substr($this->getMessage(), 0, 2)) == 'cl') {
			$this->createList();
		} else {
			$interaction = Interaction::get($this->chat);
			if (!empty($interaction) && $interaction['command'] == 'addItem') {
				$this->deleteIncomingMessage();
				$list = new Lists();
				$buttons = new Buttons();
				$params = json_decode($interaction['params'], true);
				$messageId = $params['messageId'];
				$listId = $params['listId'];
				$list->addItem($listId,
					trim(mb_strtoupper(
							mb_substr($this->getMessage(), 0, 1, 'utf-8'), 'utf-8') . mb_substr(
							$this->getMessage(), 1)));
				$this->telegram->editMessageText(
					$this->chat, $messageId,
					$this->stringListItems($listId),
					$buttons->ok($listId)
				);
			}
//			$this->unknownTeam();
		}
	}

	private function createList()
	{
		if (preg_match('/cl\s\((.+)\):(.+)/', $this->getMessage())) {
			preg_match_all('/cl\s\((.+)\):(.+)/', strtolower($this->getMessage()), $matches);
			$listTitle = trim($matches[1][0] ?? 'No title');
			$listTitle = mb_strtoupper(
					mb_substr($listTitle, 0, 1, 'utf-8'), 'utf-8') . mb_substr($listTitle, 1);
			$items = $matches[2][0];
		} elseif (preg_match('/cl:\s(.+)/', strtolower($this->getMessage()))) {
			$listTitle = "List";
			preg_match_all('/cl:\s(.+)/', strtolower($this->getMessage()), $matches);
			$items = $matches[1][0];
		}

		$items = array_map(function ($v) {
			$v = trim($v);
			return mb_strtoupper(mb_substr($v, 0, 1, 'utf-8'), 'utf-8') . mb_substr($v, 1);
		}, explode(',', $items));

		$items = array_filter($items, function ($v) {
			return !empty($v);
		});

		$items = array_unique((array)$items);

		$list = new Lists();
		$listId = $list->addList($this->chat, $listTitle);

		foreach ($items as $item) {
			$list->addItem($listId, $item);
		}

		$this->deleteIncomingMessage();

		$this->deleteMessage($this->getMessageId());

		$this->send($this->stringListItems($listId), InlineButtons::list($listId));
	}

	private function stringListItems($listId)
	{
		$list = new Lists();

		$listTitle = $list->getList($listId)['title'];

		if ($listTitle == 'List') $listTitle = "📃";

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
		$items = array_map(function ($v) use ($callbackQuery) {
			if ($callbackQuery == 'deleteItem') {
				$v['title'] = "✖️ " . $v['title'];
			} else {
				$v['title'] = ($v['completed'] ? "☑️ " : "⬜ ") . $v['title'];
			}
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
