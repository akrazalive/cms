<?php

namespace TestCMS\CMSBundle\Entities;


class DBConnection {
  
  /* PRIVATE FIELDS */
	/**
	* List of connection and driver specific arguments.
	* @access private
	* @var array
	*/
  private $_arguments;
	/**
	* Database connection handle.
	* @access private
	* @var resource
	*/
  //private $_connection;
  public $_connection; // for testing, change back!
	/**
	* The last error message.
	* @access private
	* @var string
	*/
  private $_error;
	
	
	public function __construct($arguments)
	{
		$this->clear_error();
		$this->_arguments = $arguments;
	}
	
	
	/**
	* Sets the Error Message
	* @access private
	* @param string $scope The scope of the error, generally the function in which it occured.
	* @param string $message The actual error message.
	*/
	private function set_error($scope, $message) {
		$this->_error = sprintf("%s: %s", $scope, $message);
	}
	
	
	/**
	* Clears the latest error message.
	* @access private
	*/
	private function clear_error() {
		$this->_error = "";
	}
	
	
	public function get_error() {
	  return $this->_error;
	}
  
  
  public function connect() {
		if ($this->_connection) {
			return true;
    }
		$args = array();
		if (isset($this->_arguments["hostname"])) {
		  if (isset($this->_arguments["options"]["port"]) && (strlen($this->_arguments["options"]["port"]) > 0)) {
    		$port =  ":" . $this->_arguments["options"]["port"];
  		}
  		else {
  		  $port = "";
  		}
			$server = $this->_arguments["hostname"] . $port;
			$args[] = $server;
		}
		if (isset($this->_arguments["username"])) {
			$args[] = $this->_arguments["username"];
		}
		if (isset($this->_arguments["password"])) {
			$args[] = $this->_arguments["password"];
		}
		// Initialise & connect to MySQL Server
		$this->_connection = mysqli_init();
    if (! $this->_connection) {
      $this->set_error("Connect", "mysqli_init failed");
    }
    else {
      // Set default options
      if (! $this->_connection->options(MYSQLI_OPT_CONNECT_TIMEOUT, 30)) {
        $this->set_error("Connect", "Setting MYSQLI_OPT_CONNECT_TIMEOUT failed");
      }
      else {
        try {
          // Connect to the DB server using credentials
		      switch(count($args))
		      {
			      case 1:
				      $this->_connection->real_connect($args[0]);
				      break;
			      case 2:
				      $this->_connection->real_connect($args[0], $args[1]);
				      break;
			      case 3:
    			    $this->_connection->real_connect($args[0], $args[1], $args[2]);
				      break;
		      }
		      if (! $this->_connection) {
		        $this->set_error("Connect", "Problem connecting to MySQL server: " . 
		          mysqli_connect_error());
          }
        }
        catch(Exception $exc) {
          $code = $exc->getCode();
          if (! empty($code)) {
            $this->set_error("Connect", "Problem connecting to MySQL server: " . 
		          mysqli_connect_error());
          }
        }
        
        
      }
    }
    //$res = (if (strlen($this->_error) > 0) ? true : false);
    if (strlen($this->_error) > 0) {
      $res = false; 
    }
    else {
      $res = true;
    }
		return $res;
	}
	
	
	/**
	* Closes the Database Connection
	* @access public
	* If this feature is not supported this method should return a default value of true.
	* @return bool Returns true if the database connection was successfully closed, otherwise false.
	*/
	function close() {
		if ($this->_connection) {
			$result = mysqli_close($this->_connection);
			if (! $result){
				$this->set_error("Close", mysqli_error($this->_connection));
				$result = false;
			}
		}
		else {
			$this->SetError("Close", "No connection to close.");
		}
		return $result;
	}
	
	
	/**
	* Executes an SQL statement passed in as a parameter.
	* @access public
	* If the query fails an error should be set that explains as best as possible the 
	* reason for the query failure.
	* @param string $sql The SQL statement to execute on the database.
	* @return resource If the query was successful, the result handle of the query 
	* used in result fetching functions, otherwise false.
	*/	
	function query($sql) {
	  $query_result = false;
		if ($this->connect()) {
		  $db_chosen = mysqli_select_db($this->_connection, $this->_arguments["database"]);
		  $query_result = mysqli_query($this->_connection, $sql);
		  if ( (! $db_chosen) || (! $query_result) ) {
			  // Bad query result: error.
        $this->set_error("Query", sprintf(
          "There was a problem running query: %s.  Error: %s", $sql, mysqli_error(
          $this->_connection)));
        $query_result = false;
      }
    }
		return $query_result;
	}
	
	
	/**
	* Fetches a Result Row as a numbered array
	* @access public
	* @param resource A reference to a resource handle returned by executing a query.
	* @return array Returns an array if the operation was successful, otherwise false.
	*/
	public function fetch_array($query_result) {
  	$this->clear_error();
	  $res = array();
	  try {
      //while ($row = mysqli_fetch_array($query_result, MYSQLI_NUM)) {
      while ($row = mysqli_fetch_row($query_result)) {
        $res[] = $row;
      }
    }
    catch(Exception $exc) {
      $code = $exc->getCode();
      if (! empty($code)) {
        $this->set_error("Fetch Array", sprintf("code: %d, message: ", $code, 
          $exc->getMessage()));
      }
      $res = false;
    }
    return $res;
  }
  
  
  /**
	* Fetches a Result Row as an Associative Array
	* @access public
	* If this feature is not supported an error should be set explaining this.
	* @param resource A reference to a resource handle returned by executing a query.
	* @return array Returns an associative array if the operation was successful, otherwise false.
	*/
	public function fetch_assoc_array($query_result) {
		$this->clear_error();
		$res = array();
	  try {
      while($row = mysqli_fetch_assoc($query_result)) {
        $res[] = $row;
      }
    }
    catch(Exception $exc) {
      $code = $exc->getCode();
      if (! empty($code)) {
        $this->set_error("Fetch Assoc Array", sprintf("code: %d, message: ", $code, 
          $exc->getMessage()));
      }
      $res = false;
    }
    return $res;
	}
	
	
	/**
	* Gets the Number of Rows - use? can use a count query anyway
	* Gets the number of rows returned by given result handle.
	* @access public
	* @param resource $rs A reference to a resource handle returned by executing a query.
	* @return int Returns the number of fields returned by the last executed query.
	*/
	public function row_count($query_result) {
		$this->clear_error();
		try {
      $result = mysqli_num_rows($query_result);
    }
    catch (Exception $exc) {
      $code = $exc->getCode();
      if (! empty($code)) {
        $this->set_error("Free Result", sprintf("code: %d, message: ", $code, 
          $exc->getMessage()));
      }
      $result = false;
    }
    return $result;
	}
	
	
	/**
	* Frees a Result Resource
	* Frees the resources associated with the given result handle.
	* @access public
	* @param resource A reference to a resource handle returned by executing a query.
	* @return bool Returns true if the resource handle was successfully freed.
	*/
	public function free_result(&$rs) {
		$this->clear_error();
		try {
		  $result = mysqli_free_result($rs);
		  $result = true;
	  }
	  catch (Exception $exc) {
      $code = $exc->getCode();
      if (! empty($code)) {
        $this->set_error("Free Result", sprintf("code: %d, message: ", $code, 
          $exc->getMessage()));
      }
      $result = false;
    }
	  return $result;
	}
	
	
	/**
	* Gets the Last Inserted AUTO_INCREMENT ID
	* Gets the ID of the last AUTO_INCREMENT record inserted into the databse.
	* @access public
	* If this feature is not supported an error should be set explaining this.
	* @return mixed Returns either the ID of the last inserted AUTO_INCREMENT record, or false if the 
	* last query was not an insert.
	*/
	public function insert_ID() {
		$this->clear_error();
		try {
    	$result = mysqli_insert_id($this->_connection);
		  if ($result == 0) {
			  $result = -1;
      }
    }
    catch (Exception $exc) {
      $code = $exc->getCode();
      if (! empty($code)) {
        $this->set_error("Insert ID
        ", sprintf("code: %d, message: ", $code, 
          $exc->getMessage()));
      }
      $result = -1;
    }
		return $result;
	}
		
}