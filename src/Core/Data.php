<?php

namespace Core;

class Data {
	
	cons TABLE;
	
	protected $conn;
	protected $logger;
	protected $memcached;
	
	public function __construct() {
		global $conn, $logger, $memcached;
		
		$this->conn = $conn;
		$this->logger = $logger;
		$this->memcached = $memcached;
	}

	public function lock($param) {
		$this->conn->exec("LOCK TABLES $param;");
	}

	public function unlock() {
		$this->conn->exec("UNLOCK TABLES;");
	}

	protected function prepare($sql, $criteria, $order, $offset, $limit) {
		if (isset($criteria)) {
			$arrSpecialChars = ['IS NOT NULL', 'IS NULL'];
			$arrCriteria = [];

			foreach ($criteria as $k => $v) {
				if (!in_array($v, $arrSpecialChars, true))
					$arrCriteria[] = "`$k`='$v'";
	            
	            if (in_array($v, $arrSpecialChars, true))
					$arrCriteria[] = "`$k` $v";
	        }

	        if ($arrCriteria)
	        	$sql .= " WHERE " . implode(' AND ', $arrCriteria);
		}

        if (isset($order) && !empty($order)) {
        	$sql .= " ORDER BY";

        	$orderLength = count($order) - 1;
        	$orderCount = 0;

        	foreach ($order as $k => $v) {
        		if (strtoupper($v) == 'ASC' || strtoupper($v) == 'DESC') {
        			$sql .= " `$k` $v";

        			if ($orderCount < $orderLength) {
        				$sql .= ",";
        			}

        			$orderCount ++;
        		}
	        }        	
        }

        if ($offset >= 0 && $limit > 0)
        	$sql .= " LIMIT $offset,$limit";

        return $sql;
	}

	protected function fetchAll($sql, $data, $expire = null, $shareble = false) {
		if (isset($expire)) {
			if ($shareble)
				$cache = md5($sql . serialize($data) . rand(1, \Core\System::getConfig('memcached')['endpoint']));
			
			if (!$shareble)
				$cache = md5($sql . serialize($data));

		    if ($result = $this->memcached->get($cache))
	        	return $result;
        }

	    $query = $this->conn->prepare($sql);
	    $query->execute($data);
		
 		$fetch = $query->fetchAll(\PDO::FETCH_CLASS);

 		if (isset($expire)) {
	    	$this->memcached->set($cache, $fetch, $expire);
	    }

	    return $fetch;
	}

	protected function fetchObject($sql, $data, $expire = null, $shareble = false) {
		if (isset($expire)) {
			if ($shareble)
				$cache = md5($sql . serialize($data) . rand(1, \Core\System::getConfig('memcached')['endpoint']));
			
			if (!$shareble)
				$cache = md5($sql . serialize($data));

	    	if ($result = $this->memcached->get($cache))
	        	return $result;
		}

	    $query = $this->conn->prepare($sql);
	    $query->execute($data);

	    $fetch = $query->fetchObject();

	    if (isset($expire)) {
	    	$this->memcached->set($cache, $fetch, $expire);
	    }

	    return $fetch;
	}

	public function update($field, $criteria) {
		if (!isset($field) && !isset($criteria)) {
			return false;
		}

		$arrField = $arrCriteria = $arrBind = [];

		foreach ($field as $k => $v) {
        	$arrField[] = "$k = :$k";
        	$arrBind[":$k"] = $v;
        }

		foreach ($criteria as $k => $v) {
        	$arrCriteria[] = "$k = :$k";
        	$arrBind[":$k"] = $v;
        }

        $sql = 'UPDATE ' . self::TABLE . ' SET ' . implode(',', $arrField) . ' WHERE ' . implode('AND', $arrCriteria) ;

        $stm = $this->conn->prepare($sql);

        try {
        	$this->conn->beginTransaction();
			$stm->execute($arrBind);
			$this->conn->commit();
		} catch(\PDOException $ex) {
			$this->conn->rollback();
			$this->logger->addError('PDOException: ' . $ex);
			$this->logger->addError('=>' . $sql, [$arrBind]);
		}

        return $stm->rowCount();
	}

	public function insert($field) {
		$arrField = $arrValue = $arrBind = [];

		foreach ($field as $k => $v) {
        	$arrField[] = "$k";
        	$arrValue[] = ":$k";
        	$arrBind[":$k"] = $v;
        }

		$sql = 'INSERT INTO ' . self::TABLE . ' (' . implode(',', $arrField) . ') VALUES (' . implode(',', $arrValue) . ')';

		$stm = $this->conn->prepare($sql);

		try {
			$this->conn->beginTransaction();
			$stm->execute($arrBind);

			if (array_key_exists(':id', $arrBind))
				$lastID = (int) $arrBind[':id'];

			if (!array_key_exists(':id', $arrBind))
				$lastID = $this->conn->lastInsertId();

			$this->conn->commit();
		} catch(\PDOException $ex) {
			$this->conn->rollback();
			$this->logger->addError('PDOException: ' . $ex);
			$this->logger->addError('=>' . $sql, [$arrBind]);
		}

		if (isset($lastID))
			return $lastID;
	}
}