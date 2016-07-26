<?php
$included = strtolower(realpath(__FILE__)) != strtolower(realpath($_SERVER['SCRIPT_FILENAME']));
if(!$included) die();

class BraXuS {
	
	private static $mysql_host = "localhost";
	private static $mysql_user = "";
	private static $mysql_pass = "";
	private static $mysql_data = "";
	public static $mysql_salt = "";
	private static $mysql_prefix = ""; // Shared DB hosting
	
	public static $debugEnabled = false;
	public $dbh;

	
	public function __construct() {
		try {
		$this->dbh = new PDO('mysql:dbname='.self::$mysql_data.';host='.self::$mysql_host, self::$mysql_user, self::$mysql_pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"));
		}catch (PDOException $e){
			return 'Database error';
		}
		if(phpversion() < 5.1) die("PHP needs to be on atleast version 5.1 for this to work, sorry!");
	}
		
	public function PDORows($query,$variables=NULL){
		$dbh = $this->dbh;
		$stmt = $dbh->prepare($query);
		$stmt->execute($variables);	
		return $stmt->rowCount();
	}
	
	public function PDOFetchAll($query,$variables=NULL){
		$dbh = $this->dbh;
		$stmt = $dbh->prepare($query);
		$stmt->execute($variables);	
		return $stmt->fetchAll();
	}
	
	public function PDOFetch($query,$variables=NULL){
		$dbh = $this->dbh;
		$stmt = $dbh->prepare($query);
		$stmt->execute($variables);
		return $stmt->fetch();
	}
	
	public function PDOInsert($values, $table){
		/**
		* Inserts an array with the specified options
		* @param array with values, table
		* @returns last insert id
		*/
		
		// set up
		$exec = array();
		foreach($values as $k => $v) $exec[':'.$k] = $v;
		
		// query
		$sql = "INSERT INTO ".$table." (";
		foreach($values as $k => $v) $sql .= $k.",";
		$sql = trim($sql,",");
		$sql .= ") VALUES (";
		foreach($values as $k => $v) $sql .= ":".$k.",";
		$sql = trim($sql,",");
		$sql .= ")";
		
		// Execute
		$dbh = $this->dbh;		
		$q = $dbh->prepare($sql);

		$q->execute($exec);
		//if($dbh->errorInfo()) print_r($dbh->errorCode());
		if($dbh->lastInsertId()){
		return $dbh->lastInsertId();
		}else{
			return $dbh->errorInfo();
		}
		}
	
	public function PDOUpdate($values, $table, $where, $wherevars=NULL){
		/**
		* Inserts an array with the specified options
		* @param array with values, table
		* @returns last insert id
		*/
		
		// set up
		$exec = array();
		foreach($values as $k => $v) $exec[":".$k] = $v;
		if($wherevars) foreach($wherevars as $k => $v) $exec[":".$k] = $v;
		
		$sql = "UPDATE ".$table." SET ";
		foreach($values as $k => $v) $sql .= $k." = :".$k.", ";
		$sql = trim($sql,", ");
		$sql .= " WHERE ".$where;
		// query

		// Execute
		$dbh = $this->dbh;
		$q = $dbh->prepare($sql);
		$q->execute($exec);
		return $dbh->errorCode() == "00000";
		
	}

	public function PDOReplace($values, $table){
		/**
		* Inserts an array with the specified options
		* @param array with values, table
		* @returns last insert id
		*/
		
		// set up
		$exec = array();
		foreach($values as $k => $v) $exec[':'.$k] = $v;
		
		// query
		$sql = "REPLACE INTO ".$table." (";
		foreach($values as $k => $v) $sql .= $k.",";
		$sql = trim($sql,",");
		$sql .= ") VALUES (";
		foreach($values as $k => $v) $sql .= ":".$k.",";
		$sql = trim($sql,",");
		$sql .= ")";
		
		// Execute
		$dbh = $this->dbh;		
		$q = $dbh->prepare($sql);

		$q->execute($exec);
		//return $q->errorCode();
		//if($dbh->errorInfo()) print_r($dbh->errorCode());
		if($dbh->lastInsertId()){
			return $dbh->lastInsertId();
		}else{
			return $dbh->errorInfo();
		}
		//return $sql;
	}
	
	public function PDODelete($where, $table, $variables=NULL){
		/**
		* Inserts an array with the specified options
		* @param array with values, table
		* @returns last insert id
		*/
		
		$sql = "DELETE FROM ".$table;
		$sql .= " WHERE ".$where;
		// query

		// Execute
		$dbh = $this->dbh;
		$q = $dbh->prepare($sql);
		$q->execute($variables);

	}
	
	public function checkEmail($email){
		return preg_match( "/^([a-zA-Z0-9])+([a-zA-Z0-9._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9._-]+)+$/", $email);
	}
	
}

?>