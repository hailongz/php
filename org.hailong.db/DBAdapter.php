<?php

/**
 * 数据库适配器
 */
define("DB_MYSQL",0) ;
define("DB_MSSQL",1);

class DBAdapter{
	protected $servername;
	protected $database;
	protected $username;
	protected $password;
	protected $conn=null;
	protected $isConnected = false;
		
	public function DBAdapter($servername,$database,$username,$password){
		$this->servername = $servername;
		$this->database = $database;
		$this->username = $username;
		$this->password = $password;
	}
	
	public function connect(){
		
	}
	
	public function close(){
		
	}
	
	public function free($result){
		
	}
	
	public function next($result){
		
	}
	
	public function isConnected(){
		return $this->isConnected;
	}
	
	public function affectedRows(){
		return 0;
	}
	
	public function setCharset($charset){
		
	}
	
	public function getInserId(){
		
	}
	
	public function getDatabase(){
		return $this->database;
	}
	
	public function count($result){
		
	}
}

class MYSQLAdapter extends DBAdapter{
	public function MYSQLAdapter($servername,$database,$username,$password){
		$this->DBAdapter($servername,$database,$username,$password);
	}
	
	public function connect(){
		if(!$this->isConnected){
			$this->conn = mysql_connect($this->servername,$this->username,$this->password,true) or die("数据库错误!!");
			if($this->conn){
				if(!mysql_select_db($this->database,$this->conn)){
					return false;
				}
				$this->isConnected = true;
				
			}
		}
		return $this->isConnected;
	}
	
	public function close(){
		if($this->isConnected){
			mysql_close($this->conn);
			$this->conn =null;
			$this->isConnected = false;
		}
	}
	
	public function query($query){
		return mysql_query($query,$this->conn);
	}
	
	public function free($result){
		mysql_free_result($result);
	}
	
	public function next($result){
		return mysql_fetch_array($result,MYSQL_BOTH);
	}
	
	public function affectedRows(){
		return mysql_affected_rows();
	}
	
	public function setCharset($charset){
		mysql_set_charset($charset,$this->conn);
	}
	
	public function getInsertId(){
		$rs = $this->query("SELECT LAST_INSERT_ID()");
		$id = 0;
		if($rs ){
			if($row = $this->next($rs)){
				$id = $row[0];
			}
			$this->free($rs);
		}
		return $id;
	}
	
	public function count($result){
		return mysql_num_rows($result);
	}
}

class MSSQLAdapter extends DBAdapter{
	public function MSSQLAdapter($servername,$database,$username,$password){
		$this->DBAdapter($servername,$database,$username,$password);
	}
	
	public function connect(){
		if(!$this->isConnected){
			$this->conn = mssql_connect($this->servername,$this->username,$this->password);
			if($this->conn){
				if(!mssql_select_db($this->database,$this->conn)){
					return false;
				}
				$this->isConnected = true;
			}
		}
		return $this->isConnected;
	}
	
	public function close(){
		if($this->isConnected){
			mssql_close($this->conn);
			$this->conn =null;
			$this->isConnected = false;
		}
	}
	
	public function query($query){
		return mssql_query($query,$this->conn);
	}
	
	public function free($result){
		mssql_free_result($result);
	}
	
	public function next($result){
		return mssql_fetch_array($result,MSSQL_BOTH) ;
	}
	
	public function affectedRows(){
		return mssql_rows_affected();
	}
	
	public function count($result){
		return mssql_num_rows($result);
	}
}

function newDBAdapter($type,$servername,$database,$username,$password){
	if($type == DB_MYSQL){
		return new MYSQLAdapter($servername,$database,$username,$password);
	}
	if($type == DB_MSSQL){
		return new MSSQLAdapter($servername,$database,$username,$password);
	}
}

function defaultDBAdapter($type,$servername,$database,$username,$password){
	$result = newDBAdapter($type,$servername,$database,$username,$password);
	$result->connect();
	$GLOBALS["_defaultDBAdapter"] = $result;
	return $result;
}

function getDefaultDBAdapter(){
	return $GLOBALS["_defaultDBAdapter"];
}
?>