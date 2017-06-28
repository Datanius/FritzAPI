<?php

namespace FritzAPI;

require_once __DIR__."/../config.php";

class Call {
	
	public function __construct($callInfos) {
		$callInfos = (array) $callInfos;
		foreach($callInfos as $key => $callInfo) {
			$this->{(string) $key} = $callInfo;
		}
	}

	public function getID() {
		return $this->Id;
	}

	public function getInternal() {
		if(in_array($this->Type, [3])) {
			$number =  $this->CallerNumber;
		} elseif(in_array($this->Type, [1,2])) {
			$number = $this->CalledNumber;
		}
		if($number === "") {
			return "Unbekannt";
		} else {
			return (preg_match("/^((?!49)[^0|+]).*$/", $number) !== 1) ? $number : NUMBER_PREFIX.$number;
		}
		
	}

	public function getExternal() {
		if(in_array($this->Type, [3])) {
			$number = $this->Called;
		} elseif(in_array($this->Type, [1,2])) {
			$number = $this->Caller;
		}
		if($number === "") {
			return "Unbekannt";
		} else {
			return (preg_match("/^((?!49)[^0|+]).*$/", $number) !== 1) ? $number : NUMBER_PREFIX.$number;
		}
	}

	public function getType() {
		return $this->Type;
	}

	public function getTypeName() {
		switch($this->Type) {
			case 1:
				$TypeName = "eingehend";
				break;
			case 2:
				$TypeName = "eingehend (fehlgeschlagen)";
				break;
			case 3:
				$TypeName = "ausgehend";
				break;
			default:
				$TypeName = $this->Type;
				Logging::log("Unknown Call Type", $this->Type, Logging::WARNING);
				break;
		}
		return $TypeName;
	}

	public function getDate() {
		return $this->Date;
	}

	public function getDuration() {
		return $this->Duration;
	}

	public function getPath() {
		return $this->Path;
	}

	public function getName() {
		return (strlen($this->Name) > 0) ? $this->Name : false;
	}

	public function getLastCall() {
		$con = new Connection();
		$query = "SELECT `date` FROM calls WHERE Internal = :internal AND External = :external AND ID != :id ORDER BY ID DESC LIMIT 1";
		try {
			$sql = $con->prepare($query);
			$sql->bindValue(":internal", $this->getInternal());
			$sql->bindValue(":external", $this->getExternal());
			$sql->bindValue(":id", $this->getID());
			$sql->execute();
			$date = $sql->fetch(\PDO::FETCH_OBJ)->date ?? false;
		} catch(\PDOException $ex) {
			Logging::log("Error in fetching last call", $ex->getMessage(), Logging::WARNING);
		}
		return $date ?? false;
	}

	public function isNewCall() {
		$con = new Connection();
		$query = "SELECT COUNT(*) as amount FROM calls WHERE id = :id";
		try {
			$sql = $con->prepare($query);
			$sql->bindValue(":id", $this->getID());
			$sql->execute();
			$isNewCall = (bool) ($sql->fetch(\PDO::FETCH_OBJ)->amount === "0");
		} catch (\PDOException $ex) {
			Logging::log("Error in checking new call", $ex->getMessage(), Logging::WARNING);
		}
		return $isNewCall ?? false;
	}

	public function saveCall() {
		$con = new Connection();
		$query = "INSERT INTO calls (ID, Type, Internal, External, `Date`, Duration, `Path`) 
		VALUES (:id, :type, :internal, :external, :date, :duration, :path)";
		try {
			$sql = $con->prepare($query);
			$sql->bindValue(":id", $this->getID());
			$sql->bindValue(":type", $this->getType());
			$sql->bindValue(":internal", $this->getInternal());
			$sql->bindValue(":external", $this->getExternal());
			$sql->bindValue(":date", $this->getDate());
			$sql->bindValue(":duration", $this->getDuration());
			$sql->bindValue(":path", $this->getPath());
			$success = $sql->execute();
		} catch (\PDOException $ex) {
			Logging::log("Error in saving new call", $ex->getMessage(), Logging::WARNING);
		}
		return $success ?? false;
	}

}