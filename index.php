<?php

use App\Controllers\RequestHandlerBot;

require_once "vendor/autoload.php";

if (isset($_REQUEST['language'])) {
	define('LANG', 'ENG');
	define("TEXTS", [
		'mark' => '✔️ Mark',
		'edit' => '✏️ Edit',
		'add' => '➕ Add',
		'delete' => '🗑️ Delete'
	]);
} else {
	define('LANG', 'UA');
	define("TEXTS", [
		'mark' => '✔️ Відмітити',
		'edit' => '✏️ Редагувати',
		'add' => '➕ Додати',
		'delete' => '🗑️ Видалити'
	]);
}

(new RequestHandlerBot())->execute();