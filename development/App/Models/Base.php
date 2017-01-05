<?php

class App_Models_Base {
    
	protected static $config = array(
		// sql lite:
		
		'driver'	=> 'sqllite',
        'path'		=> '/../database/cdcol-sqllite.db',
		
		
		// mysql:
		/*
		'driver'	=> 'mysql',
        'host'		=> '127.0.0.1',
        'username'	=> 'root',
        'password'	=> '1234',
        'dbname'	=> 'cdcol'
		*/
		
		// mssql:
		/*
		'driver'	=> 'mssql',
        'host'		=> '127.0.0.1',
        'username'	=> 'sa',
        'password'	=> '1234',
        'dbname'	=> 'cdcol'
		*/
    );    
    protected static $connection;
    protected $db;
	protected static function getDb () {
		if (!self::$connection) {
			$cfg = (object) self::$config;
			$options = array();

			if ($cfg->driver == 'sqllite') {
				$appRoot = MvcCore::GetRequest()->appRoot;
				if (strpos($appRoot, 'phar://') !== FALSE) {
					$lastSlashPos = strrpos($appRoot, '/');
					$appRoot = substr($appRoot, 7, $lastSlashPos - 7);
				}
				$fullPath = realpath($appRoot . $cfg->path);
				self::$connection = new PDO("sqlite:$fullPath");
				
			} else if ($cfg->driver == 'mysql') {
				if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = TRUE;
				if (defined('PDO::MYSQL_ATTR_INIT_COMMAND')) $options[PDO::MYSQL_ATTR_INIT_COMMAND] = "SET NAMES 'UTF8'";
				self::$connection = new PDO(
					"mysql:host={$cfg->host};dbname={$cfg->dbname}", 
					$cfg->username, $cfg->password, $options
				);
				
			} else if ($cfg->driver == 'mssql') {
				self::$connection = new PDO(
					"sqlsrv:Server={$cfg->host};Database={$cfg->dbname}", 
					$cfg->username, $cfg->password
				);
			}
        }
        return self::$connection;
	}
    public function __construct() {
		$this->db = self::getDb();
    }
	public function __set ($name, $value) {
		$this->$name = $value;
	}
	public function _get ($name) {
		if (isset($this->$name)) return $this->$name;
		return NULL;
	}
}

