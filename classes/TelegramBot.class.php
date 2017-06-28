<?php

namespace FritzAPI;

require_once __DIR__ . "/../config.php";

class TelegramBot {

    const URL = "https://api.telegram.org/bot" . TELEGRAM_TOKEN . "/";

    public static function sendCommand($command, $arguments = "", $method = "POST", $isError = false) {
        $ch = curl_init();
        $query = self::URL . $command;
        // Ergebnis wird als String bei curl_exec zurückgegeben
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // unterdrückt die Überprüfung des Peerzertifikats
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_URL, $query);
        if (!empty($arguments)) {
            $arguments = http_build_query($arguments);
            if ($method == "POST") {
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $arguments);
            } else {
                curl_setopt($ch, CURLOPT_URL, $query . "/?" . $arguments);
            }
        }
        $result = curl_exec($ch);
        if (!$result) {
            if(!$isError) {
            	self::sendMessageToAdmin("ERROR: der Befehl konnte nicht ausgeführt werden !", true);
            }
            return null;
        }
        //$this->logCommand($command, $arguments, $method, $result);
        $result_obj = json_decode($result);
        return $result_obj->result;
    }
    
    public static function sendMessageToAdmin($text, $isError = false) {
        self::sendCommand("sendMessage", ["chat_id" => TELEGRAM_ADMIN, "text" => $text, "parse_mode" => "HTML"], "POST", $isError);
    }

    public static function sendMessage($recipient, $text) {
    	self::sendCommand("sendMessage", ["chat_id" => $recipient, "text" => $text, "parse_mode" => "HTML"]);
    }

    public function retrieveUpdates() {
    	$updates = self::sendCommand("getUpdates", ["offset" => (int) $this->getLastUpdateID() + 1]);
    	foreach($updates as $update) {
    		$message = $update->message->text;
    		$from = $update->message->from->id;
    		preg_match("/^\/(\S*)?\s?(.*)?/", $message, $matches);
    		if(isset($matches[1])) {
    			Events::onNewCommand($from, $matches[1], !empty($matches[2]) ? explode(",", $matches[2]) : null);
    		}
    		$this->setLastUpdateID($update->update_id);
    	}
    }

    public function setLastUpdateID($lastUpdateID) {
    	$Connection = new Connection();
    	$query = "INSERT INTO informations (`key`, value) VALUES ('last_update_id', :value) ON DUPLICATE KEY UPDATE value = IF(value < VALUES(value), VALUES(value), value)";
    	try {
    		$sql = $Connection->prepare($query);
    		$sql->bindParam(":value", $lastUpdateID);
    		$success = $sql->execute();
    	} catch (\PDOException $ex) {
    		Logging::log("Error in setting last update id", $ex->getMessage(), Logging::WARNING);
    	}
    	return $success ?? false;
    }

    public function getLastUpdateID() {
    	$Connection = new Connection();
    	$query = "SELECT value FROM informations WHERE `key` = 'last_update_id'";
    	try {
    		$sql = $Connection->prepare($query);
    		$sql->execute();
    		$lastUpdateID = $sql->fetch(\PDO::FETCH_OBJ)->value ?? false;
    	} catch (\PDOException $ex) {
    		Logging::log("Error in fetching last update id", $ex->getMessage(), Logging::WARNING);
    	}
    	return $lastUpdateID ?? false;
    }

}