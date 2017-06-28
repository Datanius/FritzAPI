<?php

set_time_limit(300);

require_once __DIR__."/../classes/Autoloader.class.php";

new FritzAPI\Autoloader();

use FritzAPI\{
	Fritzbox, Events
};

$Fritzbox = new Fritzbox();

$calls = $Fritzbox->getCalls();

$latestCallID = $Fritzbox->getLatestCallID();

foreach($calls as $call) {
	if($call->getID() > $latestCallID && in_array($call->getType(), [1,2,3]) {
		$call->saveCall();
		Events::onNewCall($call);
	}
}

