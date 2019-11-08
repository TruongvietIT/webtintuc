<?php

class Mysql
{
	protected $_dbhost;
	protected $_dbuser;
	protected $_dbname;
	protected $_dbpassword;
	protected $_dbport 				= '3306';
	protected $_dbcharset 			= 'utf8';
	protected $_connection;
	protected $_error;
	protected $_errorCode;
	protected $_exception;
	protected $_affectedRows	   = 0;
	protected $_autoCommit         = false;
	protected $_isolationLevel     = 'READ COMMITTED';
	protected $_transactionStarted = false;

	function __construct($config = array())
	{
		if (true === isset($config['dbhost'])){
			$this->_dbhost = $config['dbhost'];
		}
		
		if (true === isset($config['dbuser'])){
			$this->_dbuser = $config['dbuser'];
		}
		
		if (true === isset($config['dbpassword'])){
			$this->_dbpassword 	= $config['dbpassword'];
		}
		
		if (true === isset($config['dbname'])){
			$this->_dbname = $config['dbname'];
		}
		
		if (true === isset($config['dbport'])){
			$this->_dbport = $config['dbport'];
		}
		
		
	}
	public function connect()
	{
		if (false === isset($this->_connection)){
			$this->_connection = mysqli_connect ($this->_dbhost, $this->_dbuser, $this->_dbpassword, $this->_dbname);

			if (null === $this->_connection)
			{
				die ('Server is busy');
			}
			else
			{
                if (!mysqli_connect_errno())
				{
                    mysqli_query($this->_connection, 'SET Names utf8');
				}
				else{
                    $this->_error = mysqli_connect_error();
				}
			}
		}
	}
	
	
	public function close()
	{
		mysqli_close ($this->_connection);
		unset($this->_connection);
	}	
	
	/**
	 * Turns autocommit on.  After this function returns,
	 * subsequent SQL statements will take effect immediately.
	 * @return void
	 */
	function turnOnAutocommit()
	{
		if (!mysql_query('set autocommit=1', $this->_connection))
		{
			$this->_error = mysql_error();
		}
	}
	
	/**
	 * Turns autocommit off.  After this function returns,
	 * subsequent SQL statements will not take effect immediately.
	 * @return void
	 */

	function turnOffAutocommit()
	{
		if (!mysql_query('set autocommit=0', $this->_connection))
		{
			$this->_error = mysql_error();
		}
	}

	/**
	 * Begins a transaction.  Theoretically, calls to execute
	 * are not committed until you call commit.
	 *
	 * Transactions are automatically rolled back when you close the connection,
	 * or when the script ends, whichever is soonest.
	 * You need to explicitly call ::commit() to commit the transaction,
	 * or ::rollBack() to abort it.
	 *
	 */

	function beginTransaction()
	{

		// Transaction is the default dehaviour in MySQL server
		// We will use this method to emulate if auto commit mode is used or not
		// In this case we must call ::commit() explicitly to make the changes in the database
		// $this->_autoCommit = false;
		
		if (!$this->_transactionStarted)
		{
			if (!mysql_query('set session transaction isolation level '.$this->_isolationLevel, $this->_connection))
			{
				$this->_error = mysql_error();
			}
			else
			{
				if (!mysql_query('begin', $this->_connection))
				{
					$this->_error = mysql_error();
				}
				else
				{
					$this->_transactionStarted = true;
				}

			}

		}

	}
	
	/**
	 * Makes all changes made since the previous commit/rollback permanent
	 * and releases any database locks currently held by the Connection.
	 * This method should be used only when auto-commit mode has been disabled.
	 *
	 * @throws Sone_Database_Exception
	 * @see    setAutoCommit(boolean)
	 *
	 */
	function commit()
	{
		if ($this->_transactionStarted)
		{

			if (!mysql_query('commit', $this->_connection))
			{
				$this->_error = mysql_error($this->_connection);
			}
			else
			{
				$this->_transactionStarted = false;
			}

		}

	}
	
	/**
	 * Drops all changes made since the previous commit/rollback
	 * and releases any database locks currently held by this Connection.
	 * This method should be used only when auto- commit has been disabled.
	 *
	 * @throws Sone_Database_Exception
	 * @see    setAutoCommit(boolean)
	 *
	 */
	function rollback()
	{
		if ($this->_transactionStarted)
		{
			if (!mysql_query('rollback', $this->_connection))
			{
				$this->_error = mysql_error($this->_connection);
			}
			else
			{
				$this->_transactionStarted = false;
			}

		}

	}	

	public function prepareSQL($sql, $args = null)
	{
		if (null !== $args){
			$this->connect();
			foreach ($args as $key => $value){
				$sql =  str_replace(':'. $key, is_int($value) ? $value : "'". $this->quote($value) ."'", $sql);
			}
            $this->close();
		}
		return $sql;
	}
	
	public function quote($value)
	{
		return mysqli_real_escape_string($this->_connection, $value);
	}
	
	public function execute($sql, $countRows = false)
	{
		$this->connect();
		$resultSet = mysqli_query($this->_connection, $sql);
		if (!$resultSet)
		{
			$this->_error     = mysqli_error($this->_connection);
			$this->_errorCode = mysqli_errno($this->_connection);
            $this->close();
			return;
		}

		// count number of rows
		if (true === $countRows && (preg_match("#^\s*(select|show)#i", $sql)))
		{
			$this->_affectedRows = $resultSet->num_rows;
		}
		elseif (true === $countRows && (preg_match('#^\s*(insert|update|delete)#i', $sql)))
		{
            //file_put_contents(Context::getInstance()->getBasePath(). 'query/query.'.date('d.m.Y'), '--'. date('H:i:s'). "\n". $sql.';'. "\n\n", FILE_APPEND);
			$this->_affectedRows = mysqli_affected_rows($this->_connection);
		}
		
		return $resultSet;
	}
	
	public function getAffectedRows()
	{
		return $this->_affectedRows;
	}

	public function fetch($resultSet)
	{
		return mysql_fetch_array($resultSet, MYSQL_ASSOC);
	}

	public function fetchRow($sql)
	{
		$resultSet  = $this->execute($sql, false);
        if (!empty($resultSet)){
			$row = mysqli_fetch_assoc($resultSet);
			mysqli_free_result($resultSet);
			if (false === $row)
			{
				return null; // Empty row when no more row returned
			}
			return $row;	
		}
		return null;

	}
	
	function fetchAll($sql = null, $bool = false)
	{
		$resultSet  = $this->execute($sql, $bool);
		if (!empty($resultSet)){
            $rowList       = array(); // Two-dimentional array
            while ($row    = mysqli_fetch_array($resultSet))
            {
                $rowList[] = $row;
            }
            mysqli_free_result($resultSet);
            return $rowList;
        }
        return null;
	}
	
	public function getLastInsertedId()
	{
		$this->connect();
		return mysqli_insert_id($this->_connection);
	}
}
?>