<?php

namespace App\Models;

use App\Core\Database;

class Lists
{
	private $db;

	public function __construct()
	{
		$this->db = Database::instance()->getDbh();
	}


	public function addList($chat, $title): int
	{
		$stmt = $this->db->prepare("
			INSERT INTO `list` (`chat`, `title`) VALUES (:chat, :title)
		");
		$stmt->execute([
			'chat' => $chat,
			'title' => $title
		]);
		return $this->db->lastInsertId();
	}

	public function getList($id): array
	{
		$stmt = $this->db->prepare("
			SELECT * 
			FROM `list` 
			WHERE `id` = :id
		");
		$stmt->execute([
			'id' => $id
		]);
		return $stmt->fetchAll()[0] ?? [];
	}

	public function getAll($chat): array
	{
		$stmt = $this->db->prepare("
			SELECT * 
			FROM `list` 
			WHERE `chat` = :chat
		");
		$stmt->execute([
			'chat' => $chat
		]);
		return $stmt->fetchAll();
	}

	public function addItem($listId, $title): int
	{
		$stmt = $this->db->prepare("
			INSERT INTO `item` (list_id, title) VALUES (:listId, :title)
		");
		$stmt->execute([
			'listId' => $listId,
			'title' => $title
		]);
		return $this->db->lastInsertId();
	}

	public function deleteItem($id): void
	{
		$stmt = $this->db->prepare("
			DELETE FROM `item` WHERE `id` = :id
		");
		$stmt->execute([
			'id' => $id
		]);
	}

	public function completed($id): void
	{
		$stmt = $this->db->prepare("
			UPDATE `item` 
			SET `completed` = IF(`completed` = 1, 0, 1)
			WHERE `id` = :id
		");
		$stmt->execute([
			'id' => $id
		]);
	}

	public function getItems($listId)
	{
		$stmt = $this->db->prepare("
			SELECT * 
			FROM `item`
			WHERE `list_id` = :listId
			ORDER BY `id`
		");
		$stmt->execute([
			'listId' => $listId
		]);
		return $stmt->fetchAll();
	}

	public function getListByItemId($id)
	{
		$stmt = $this->db->prepare("
			SELECT l.* 
			FROM `list` l, 
			     `item` i
			WHERE l.`id` = i.`list_id`
			AND i.`id` = :id
		");
		$stmt->execute([
			'id' => $id
		]);
		return $stmt->fetchAll()[0] ?? [];
	}

	public function updateTitle($id, $title)
	{
		$stmt = $this->db->prepare("
			UPDATE `list`
			SET `title` = :title
			WHERE `id` = :id
		");
		$stmt->execute([
			'id' => $id,
			'title' => $title
		]);
	}
}