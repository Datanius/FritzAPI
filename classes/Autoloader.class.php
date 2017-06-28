<?php

namespace FritzAPI;

/**
 * Autoloader
 *
 * @author Robin
 */
class Autoloader {
    
    public function __construct() {
        spl_autoload_register([$this, "load"]);
    }
    
    private function load($class_name) {
        $explode = explode('\\', $class_name, 2);
        $app = $explode[0];
        $namespace = (isset($explode[1]) && $explode[1] ? $explode[1] : '');
        if($app === "FritzAPI" && $namespace) {
            $filename = __DIR__ . '/' . str_replace("\\", "/", $namespace) . '.class.php';
            if (file_exists($filename)) {
                require_once $filename;
                if (class_exists($class_name)) {
                    return true;
                }
            }
            return false;
        }
        return false;
    }
    
}
