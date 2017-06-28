<?php

require_once __DIR__."/classes/Autoloader.class.php";

echo "<pre>";
$TelegramBot = new FritzAPI\TelegramBot();
$TelegramBot->retrieveUpdates();
echo "</pre>";


