<?php
class Nosql_Mongo
{
	const DEFAULT_HOST 			= '127.0.0.1';
    const DEFAULT_PORT 			= 27017;
    const DEFAULT_PERSISTENT 	= true;
    const DEFAULT_DBNAME 		= 'test';
    
    protected $_options;
	protected $_conn;
	protected $_databases;
	protected $_collections;
	protected $_messages;
	
	public function __construct($options = array())
	{
		if (!extension_loaded('mongo')) {
            throw new Exception('Mongo extension does not appear to be loaded');
        }
        
        $this->_options  =  $options;
        
		if (false === isset($this->_options['host'])) {
            $this->_options['host'] 	= self::DEFAULT_HOST;
        }

        if (false === isset($this->_options['port'])) {
            $this->_options['port'] 	= self::DEFAULT_PORT;
        }

        if (false === isset($this->_options['dbname'])) {
            $this->_options['dbname'] 	= self::DEFAULT_DBNAME;
        }
        
		if (false === isset($this->_options['option']) || !is_array($this->_options['option'])) {
            $this->_options['option'] 	= array();
        }
        
        $this->connect()->selectDB($this->_options['dbname']);
	}
	
	public function connect()
	{
		if (false === isset($this->_conn)){
			$connectionString 	= 'mongodb://'. (isset($this->_options['username']) && isset($this->_options['password']) ? $this->_options['username']. ':'. $this->_options['password'].'@' : ''). 
								  $this->_options['host']. ':'. $this->_options['port'];

			$this->_conn  		= new Mongo($connectionString, $this->_options['option']);
		}
		return $this->_conn;
	}
	
	public function selectDB($dbName)
	{
		if ($dbName === null){
			$dbName = $this->_options['dbname'];
		}
		if (false === isset($this->_databases[$dbName])){
			$this->_databases[$dbName]	=  $this->_conn->selectDB($dbName);
		}
		return $this->_databases[$dbName];
	}
	
	public function selectCollection($collectionName, $dbName = null)
	{	
		if (false === isset($this->_collections[$collectionName])) {
			if (false === ($this->_collections[$collectionName]  =  $this->selectDB($dbName)->selectCollection($collectionName))){
				$this->selectDB($dbName)->createCollection($collectionName);
				$this->_collections[$collectionName]  =  $this->selectDB($dbName)->selectCollection($collectionName);
			}
		}
		return $this->_collections[$collectionName];
	}
	
	public function dropCollection($collectionName, $dbName = null)
	{
		return $this->selectDB($dbName)->selectCollection($collectionName)->drop();
	}
	
	public function close()
	{
		if ($this->_conn instanceof Mongo){
			$this->_conn->close();
			unset($this->_conn);
		}
	}
	
	public function __destruct() {
       $this->close();
    }
	
	public function id($obj) 
	{
		if (empty($obj)){
			return null;
		}
        if ($obj instanceof MongoId) {
            return $obj;
        }
        if (is_string($obj)) {
            return new MongoId($obj);
        }
        if (is_array($obj)) {
            return $obj['_id'];
        }
        return new MongoId($obj->_id);
    }
    
    public function regex($patern, $flag = 'is')
    {
    	return new MongoRegex("/". $patern ."/" . $flag); 	
    }
	
	public function find($query = array(), $options = array(), $collectionName, $dbName = null) 
	{
		$fields 	= isset($options['fields']) ? $options['fields'] : array();        
        $result 	= $this->selectCollection($collectionName, $dbName)->find($query, $fields);
		if (isset($options['skip']) && $options['skip'] !== null) {
            $result->skip($options['skip']);
        }
        if (isset($options['sort']) && $options['sort'] !== null) {
            $result->sort($options['sort']);
        }
        if (isset($options['limit']) && $options['limit'] !== null) {
            $result->limit($options['limit']);
        }
        return $result;
    }
    
    public function findOne($query = array(), $options = array(), $collectionName, $dbName = null)
    {
    	return $this->selectCollection($collectionName, $dbName)->findOne($query, $options);
    }
    
    public function findId($id, $collectionName, $dbName = null)
    {
        if (!is_array($id)) {
            $id = array('_id' => $this->id($id));
        }
        return $this->selectCollection($collectionName, $dbName)->findOne($id);
    }
    
	public function count($query = array(), $collectionName, $dbName = null) 
	{
        $collection  =  $this->selectCollection($collectionName, $dbName);
        return $query ? $collection->find($query)->count() :
            			$collection->count();
        
    }
    
    public function save($data, $options, $collectionName, $dbName = null) 
    {
        return $this->selectCollection($collectionName, $dbName)->save($data, $options);
    }
    
    public  function insert($data, $options = array(), $collectionName, $dbName = null) 
    {
         return $this->selectCollection($collectionName, $dbName)->insert($data, $options);
    }

    public function lastError($dbName) 
    {
       	return $this->selectDb($dbName)->lastError();
    } 

    
	public function update($criteria, $newObj, $options = array(), $collectionName, $dbName = null) 
	{
        if ($options === true) {
            $options = array('upsert' => true);
        }
        if (false === isset($options['multiple'])) {
            $options['multiple'] = false;
        }
        return $this->selectCollection($collectionName, $dbName)->update($criteria, $newObj, $options);
    }
    
    
	public function upsert($criteria, $newObj, $collectionName, $dbName = null) 
	{
        return $this->update($criteria, $newObj, true, $collectionName, $dbName);
    }
    
	public function remove($criteria, $justOne = false, $collectionName, $dbName = null) 
	{
        if (!is_array($criteria)) {
            $criteria = array('_id' => $this->id($criteria));
        }
        return $this->selectCollection($collectionName, $dbName)->remove($criteria, $justOne);
    }

    public function drop($collectionName, $dbName = null) 
    {
        return $this->selectCollection($collectionName, $dbName)->drop();
    }

    public function batchInsert($array, $collectionName, $dbName = null) 
    {
        return $this->selectCollection($collectionName, $dbName)->batchInsert($array);
    }
    
	public function ensureIndex($keys, $options = array(), $collectionName, $dbName = null) 
	{
        return $this->selectCollection($collectionName, $dbName)->ensureIndex($keys, $options);
    }

    public function ensureUniqueIndex($keys, $options = array(), $collectionName, $dbName = null) 
    {
        $options['unique'] = true;
        return $this->selectCollection($collectionName, $dbName)->ensureIndex($collection, $keys, $options);
    }
    
    public function getIndexInfo($collectionName, $dbName = null) 
    {
        return $this->selectCollection($collectionName, $dbName)->getIndexInfo();
    }


    public function deleteIndexes($collectionName, $dbName = null) 
    {
       	return $this->selectCollection($collectionName, $dbName)->deleteIndexes();
    }
    
}