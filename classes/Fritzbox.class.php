<?php

namespace FritzAPI;

require_once __DIR__."/../config.php";

class Fritzbox {

	const X_AVM_DE_OnTel = [
		"uri" => "urn:dslforum-org:service:X_AVM-DE_OnTel:1",
		"location" => "http://fritz.box:49000/upnp/control/x_contact"
	];

	function __construct() {

	}

	private function callMethod($methodName, $arguments, $package) {
		$client = new \SoapClient(null, [
			"location" => $package["location"],
			"uri" => $package["uri"],
			"noroot" => true,
			"login" => FRITZBOX_USERNAME,
    		"password" => FRITZBOX_PASSWORD, 
			]
		);
		
		if(isset($arguments)) {
			$soapArguments = [];
			foreach($arguments as $key => $argument) {
				$soapArguments[] = new \SoapParam($argument, $key);
			}
			return $client->{(string) $methodName}(...$soapArguments);
		} else {
			return $client->{(string) $methodName}();
		}
	}

	private function fetchInfos($content, $tag) {
		$DOMDocument = new \DOMDocument();
		$DOMDocument->loadXML($content);
		$DOMXpath = new \DOMXpath($DOMDocument);
		$nodes = $DOMXpath->query("//".$tag);

		$results = [];

		foreach($nodes as $node) {
			$result = [];
			foreach($node->childNodes as $childNode) {
				$result[$childNode->tagName] = $childNode->nodeValue;
			}
			$results[] = (object) $result;
		}
		return $results;
	}

	public function getDeflections() {

		$result = $this->callMethod("getDeflections", null, self::X_AVM_DE_OnTel);

		return $this->fetchInfos($result, "Item");

	}

	public function getDeflection($id) {
		$deflections = $this->getDeflections();
		foreach ($deflections as $deflection) {
			if((int) $deflection->DeflectionId === $id) {
				return $deflection;
			}
		}
	}

	public function setDeflectionEnable($id, $enable) {

		$result = $this->callMethod("setDeflectionEnable", ["NewDeflectionId" => $id, "NewEnable" => $enable], self::X_AVM_DE_OnTel);

		return (int) $this->getDeflection($id)->Enable;
		
	}

	public function getCalls() {
		
		$result = $this->callMethod("GetCallList", null, self::X_AVM_DE_OnTel);

		$content = file_get_contents($result);

		$infos = $this->fetchInfos($content, "Call");

		$calls = [];

		foreach($infos as $info) {
			$calls[] = new Call($info);
		}

		return $calls;
	}

	public function getLatestCallID() {
		$con = new Connection();
		$query = "SELECT ID FROM calls ORDER BY ID DESC LIMIT 1";
		try {
			$sql = $con->prepare($query);
			$sql->execute();
			$latestCallID = $sql->fetch(\PDO::FETCH_OBJ)->ID;
		} catch (\PDOException $ex) {
			Logging::log("Error in fetching latestCallID", $ex->getMessage(), Logging::WARNING);
		}
		return $latestCallID ?? false;
	}

	public function getPhoneBook() {
		$result = $this->callMethod("GetPhonebook", ["NewPhonebookID" => 0], self::X_AVM_DE_OnTel);
		return $result;
	}


}