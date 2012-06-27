<!-- Mysql.class.php loaded -->
<?php

class Mysql{
	var $connection;
	var $address;
	var $username;
	var $password;
	var $result;
	var $database;
	var $sql;
	var $error;
	
	function __construct($address,$username,$password,$database){
		$this->address  = $address;
		$this->username = $username;
		$this->password = $password;
		$this->database = $database;
		$this->connectToDatabase();
		$this->selectDatabase($this->database);
	}
	
	function countRows(){
		return mysql_num_rows($this->result);;
	}
	
	private function connectToDatabase(){
		print '<!-- Connected to Database -->';
		$this->connection = mysql_connect($this->address,$this->username,$this->password) or die(mysql_error());
	}
	
	function selectDatabase($name){
		mysql_select_db($this->database, $this->connection);
	}
	
	function query($sql){
		$this->sql = $sql;//NEED TO PUT IN SECURITY
		$this->result = mysql_query($this->sql, $this->connection);
		if (!$this->result) {
			$this->error = mysql_error();
			return false;		
		}else {
			return true;
		}
	}

	function getResult(){
		return $this->result;
	}	
	
	//Mysql Real Escape String function time and space saver.
	function c($string){
		return mysql_real_escape_string($string);
	}
	
	function freeResult(){
		mysql_free_result($this->result);
	}
	function getSql(){
		return $this->sql;
	}
}
?>