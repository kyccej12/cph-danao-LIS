<?php
	class myConnector {
		private $_connection;
		private static $_instance;
		private $_host = "localhost";
		private $_username = "root";
		private $_password = "";
		private $_database = "ppp_danao";
		
		public static function getInstance() {
			if(!self::$_instance) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
		
		public function __construct() {
			$this->_connection = new mysqli($this->_host, $this->_username, 
				$this->_password, $this->_database);
		
			
			if(mysqli_connect_error()) {
				trigger_error("Failed to conencto to MySQL: " . mysqli_connect_error(),
					 E_USER_ERROR);
			}
		}
		private function __clone() { }
		
		public function getConnection() {
			return $this->_connection;
		}
	}
	
	class myDB extends myConnector {

		public static function dbquery($sql) {
			return @parent::getInstance()->getConnection()->query($sql);
		}
		
		public static function countRows($string) {
			$lastChar = substr($string,-1); 
			if($lastChar === ';') {
				$string = substr($string,0,-1);
			}
			
			$newQueryString = "SELECT COUNT(*) FROM ($string) a;";
			list($numRows) = @parent::getInstance()->getConnection()->query($newQueryString)->fetch_array();
			return $numRows;
		}
		
		public static function getArray($sql) {
			return @parent::getInstance()->getConnection()->query($sql)->fetch_array(MYSQLI_BOTH);
		}
		
		public static function escapeString($string) {
			return @parent::getInstance()->getConnection()->escape_string($string);
		}
		
	}	

?>