<?php

use App\Controllers\RequestHandlerBot;

require_once "vendor/autoload.php";

echo (new RequestHandlerBot())->setWebhook('https://mysite.com/');
