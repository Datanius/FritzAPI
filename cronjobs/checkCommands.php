<?php

require_once __DIR__."/../classes/Autoloader.class.php";

new FritzAPI\Autoloader();

use FritzAPI\{
	TelegramBot
};

$TelegramBot = new TelegramBot();
$TelegramBot->retrieveUpdates();