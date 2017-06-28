<?php

namespace FritzAPI;

require_once __DIR__.'/../config.php';

class Connection extends \PDO {
    
    public function __construct() {
        parent::__construct('mysql:host='.DB_HOST.';dbname='.DB_DATABASE, DB_USERNAME, DB_PASS, array());
        if($this->initDB() === false) {
            Logging::log("Unable to initDB in Connection.class.php", "", Logging::CRITICAL);
        }
    }

    private function initDB() {
    	$query = "CREATE TABLE IF NOT EXISTS calls (
	    	ID int NOT NULL, 
	    	Type int, 
	    	Internal VARCHAR(100), 
	    	External VARCHAR(100), 
	    	`Date` VARCHAR(100), 
	    	Duration TIME, 
	    	`Path` TEXT,
	    	PRIMARY KEY(ID)
    	);";
        $query .= "CREATE TABLE IF NOT EXISTS informations (
            ID int NOT NULL,
            `key` VARCHAR(400) NOT NULL,
            value TEXT,
            PRIMARY KEY(ID),
            UNIQUE(`key`)
        );";
    	try {
            $success = $this->exec($query);
        } catch (\PDOException $ex) {
            Logging::log("Unable to initDB in Connection.class.php", "", Logging::NOTICE);
        }
        return $success ?? false;
    }
    
}
