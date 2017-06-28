<?php

namespace FritzAPI;

class Events {

	/**
	* This method will be executed when a new call is registered
	* @param Call $call The new call
	**/

	public static function onNewCall($call) {
	}

	/**
	* This method will be executed when a new command is registered
	* @param string $from The Telegram ID of the user
	* @param string $command The command
	* @param string[] $parameters The parameters of the command
	**/

	public static function onNewCommand($from, $command, $parameters) {
	}

}