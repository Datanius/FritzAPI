<?php

namespace FritzAPI;

class Logging {
    
    const NOTICE = "NOTICE";
    const WARNING = "WARNING";
    const CRITICAL = "CRITICAL";
    const PATH = __DIR__."/../logfile.log";
    
    public static function log($info, $content, $level) {
        if(file_exists(self::PATH)) {
            $fileContent = file_get_contents(self::PATH);
        }
        $fileContent = (isset($fileContent) && strlen($fileContent)) > 0 ? unserialize($fileContent) : [];
        $fileContent[] = [
            "time" => time(),
            "info" => $info,
            "level" => $level,
            "content" => serialize($content),
        ];
        file_put_contents(self::PATH, serialize($fileContent));
        if($level !== self::NOTICE) {
            TelegramBot::sendMessageToAdmin("Logging: ".$info);
        }
    }
    
}
