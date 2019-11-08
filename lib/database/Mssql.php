<?php
class MSSQL {
	
	protected $_dbname;	 
	protected $_dbhost;
	protected $_dbport = 1433;
	protected $_dbuser;
	protected $_dbpassword;	
	protected $_dbcharset = 'UTF-8';
	protected $_connection;
	public 	  $_numRows;
	

	// param array $config
	public function __construct($config = null) 
	{
		
		if (isset($config['dbname'])){
			$this->_dbname = $config['dbname'];
		}
		
		if (isset($config['dbhost'])){
			$this->_dbhost= $config['dbhost'];
		}
		
		if (isset($config['dbport'])){
			$this->_port = $config['dbport'];
		}
		
		if (isset($config['dbuser'])){
			$this->_dbuser = $config['dbuser'];
		}
		
		if (isset($config['dbpassword'])){
			$this->_dbpassword = $config['dbpassword'];
		}
		
		
    }
	
	public function connect()
	{
		if (false === isset($this->_connection)){

			try {
				$this->_connection = mssql_connect($this->_dbhost, $this->_dbuser, $this->_dbpassword);
			}
			catch (Excetion $ex) {
				echo  $ex->getMessage();
			}
		
			if (!$this->_connection){ 
				die('Could not connect to SQL server!');
			}
		
			if (false === mssql_select_db($this->_dbname, $this->_connection)){ 
				die('Could not select database '. $this->_dbname);
			}
		}
	}
	
	public function close()
	{
		mssql_close($this->_connection);
		unset($this->_connection);
	}
	
	public function quote($value)
	{	
		return str_replace("'", "''", trim($value));
	}
	
	public function prepareSQL($sql, $params)
	{
		foreach ($params as $key => $value)
		{
			$sql = str_replace(':'. $key, is_numeric($value) ? $value : "N'". $this->quote($value) ."'", $sql);
		}
		return $sql;
	}
		
   	public function execute($sql, $countAffectedRow = false)
   	{
		$result	= mssql_query($sql);
		
   		if (true === $countAffectedRow && (preg_match("#^\s*select#i", $sql) || preg_match("#^\s*show#i", $sql)))
   		{
   			$this->_numRows = mssql_num_rows($result);
   		}
   		elseif (true === $countAffectedRow && (preg_match('#^\s*insert#i', $sql) || preg_match('#^\s*update#i', $sql) || preg_match('#^\s*delete#i', $sql)))
   		{
   			$this->_numRows = mssql_rows_affected($this->_connection);
   		}
		
   		return $result;
   	}
	
	public function fetch($result)
	{
		return mssql_fetch_array($result);
	}
	
	
	public function fetchAll($sql)
	{
		$result  = $this->execute($sql);
		if ($result){
		
			$rows 		= array();
			while($row 	= $this->fetch($result)){	
				$rows[] = $row;
			}
			mssql_free_result($result);
			return $rows;
		}
		return null;
	}
	
	public function fetchRow($sql)
	{
		$result  = $this->execute($sql);
		return $this->fetch($result);
	}
	
	public function fetchAllSP($spName, $params = null){
		$result = $this->callSP($spName, $params);
		if ($result){
		
			$rows 		= array();
			while($row 	= $this->fetch($result)){	
				$rows[] = $row;
			}
			mssql_free_result($result);
			return $rows;
		}
		return null;
	}
	
	public function fetchRowSP($spName, $params = null){
		$result = $this->callSP($spName, $params);
		return $this->fetch($result);
	}
	
  	public function initSP($spName)
	{
   		return mssql_init($spName, $this->_connection);
   	}

   	public function executeSP($stmt)
	{
   		return mssql_execute($stmt);
   	}

	public function bind($stmt, $param)
	{
		$isOutput	= isset($param['is_output']) 	? $param['is_output'] 	: false;
   		$isNull		= isset($param['is_null']) 		? $param['is_null'] 	: false;
   		$type		= isset($param['type']) 		? $param['type'] 		: SQLVARCHAR;
   		$maxlen		= isset($param['maxlen']) 		? $param['maxlen'] 		: -1;
		$name		= '@'. $param['name'];
		$value		= $param['value'];		
   		
		mssql_bind($stmt, $name, $value, $type, $isOutput, $isNull, $maxlen);
	}
   
   	public function callSP($spName, $params = null)
   	{
   		//Call Stored Procedure
   		$stmt  =  $this->initSP($spName);
   		
		if (null !== $params){
   			foreach ($params as $param) { 
				$this->bind($stmt, $param); 
			}
   		}
		
   		$results = $this->executeSP($stmt);
		mssql_free_statement($stmt);
	
		return $results;		
   	}
  

}
?>