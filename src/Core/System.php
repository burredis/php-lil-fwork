<?php

namespace Core;

class System {

	protected static function microtime2str() {
        list($usec, $sec) = explode(" ", microtime());
        return (string) (str_pad((string) $sec, 10, '0') . str_pad((string) round((float) $usec * 1000000), 6, '0'));
    }

    public static function newID() {
        $first_id = self::microtime2str();
        $second_id = self::microtime2str();
        while ($second_id == $first_id) {
            $second_id = self::microtime2str();
        }
        return $second_id;
    }

    public static function setSessionID($uid) {
        $config = self::getConfig('memcached');
        ini_set('session.save_handler', 'memcached');
        ini_set('session.save_path', $config['host'].':'.$config['port']);
        session_id('UID'.$uid);
        session_start();
    }

    public static function setSession($name, $data) {
        return $_SESSION[$name] = $data;
    }

    public static function getSession($name) {
        if (isset($_SESSION[$name]))
            return $_SESSION[$name];

        return self::setSession($name, '');
    }
    
    public static function removeSession() {
        session_destroy();
    }

    public static function getConfig($name) {
        global $config;
        
        if (array_key_exists($name, $config))
            return $config[$name];

        return null;
    }

}