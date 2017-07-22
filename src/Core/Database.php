<?php

namespace Core;

class Database {
	
	protected $config;
    protected $conn;

	public function __construct($config) {
        $this->config = $config;
	}

	public function connect() {
        if (!isset($this->conn)) {
            $config = $this->config;
            
            $this->conn = new \PDO($config['driver'].':host='.$config['host'].';dbname='.$config['dbname'].';charset='.$config['charset'], $config['username'], $config['password']);
            $this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_ASSOC);
            $this->conn->exec('SET time_zone=`'.(new \DateTime('now', new \DateTimeZone(date_default_timezone_get())))->format('P').'`;');
            return $this->conn;
        }

        return $this->conn;
    }

    public function close() {
        unset($this->conn);
    }
}