<?php
/**
 *  ---------------------------------------------------------------
 *  @file dbase.php
 *  ---------------------------------------------------------------
 *  @brief A class that encapsulates a database
 *  @endbrief
 *  ---------------------------------------------------------------
 *	@details  
 *  This class handles calls to access a mysql database.
 *  @enddetails
 *  
 *  @cond DEFINE_AUTHOR
 *  @Author Jojo Kahanding
 *  @date May 03, 2014 08:02 PM
 *  Copyright 2011 Jojo Kahanding - All World rights reserve
 *  @endcond
*/
 
 require_once("common.php");

 class CDbase
 {
 	
	//constuctor class
	/**
	 *  @brief constructor
	 *  
	 *  @param [in] $hostname database location
	 *  @param [in] $databaseName database name
	 *  @param [in] $user database user
	 *  @param [in] $password password of $user
	 *  @return a class instance
	 *  
	 *  @details Instantiates this object
	 */	
	function __construct($hostname, $databaseName, 
			$user, $password)
	{
		$this->m_hostname = $hostname;
		$this->m_databaseName = $databaseName;
		$this->m_user = $user;
		$this->m_password = $password;
		$this->m_connection = FALSE;
		$this->m_resultSet = FALSE;
		$this->m_countResults = 0;
		$this->m_currentPosResultSet = 0;
	}

	//destructor class
	/**
	 *  @brief destructor
	 *  
	 *  @return Nothing
	 *  
	 *  @details Destroys and does object clean up.
	 */
	function __destruct()
	{
		$this->close();
	}
	/**
	 *  @brief Performs queries to a database.
	 *  
	 *  @param [in] $sqlStatement sql statement
	 *  @return true if succesful otherwise false
	 *  
	 *  @details A database query is made using 
	 *  $sqlStatement. True is returned if the query
	 *  is succesful otherwise false.
	 *  @enddetails
	 */
	 function query($sqlStatement)
	{
		// open connection
		if (!$this->open())
			return FALSE;
		
		$this->m_resultSet = mysql_query($sqlStatement, 
			$this->m_connection);
		if (FALSE == $this->m_resultSet)
			return FALSE;
		// initialize stats
		$this->m_countResults = mysql_num_rows(
			$this->m_resultSet);
		$this->m_currentPosResultSet = 0;
		return TRUE;
	}
	/**
	 *  @brief Retrieves the current contents of a result set.
	 *  
	 *  @return an array indexed by fieldnames from a result set
	 *  otherwise FALSE if an error or eos (end of search)
	 *  is met.
	 *  @endreturn
	 *  
	 *  @details Fetches a record in the current a result set
	 */
	function fetch()
	{
		// empty result set
		if (FALSE == $this->m_resultSet)
			return FALSE;
		// eos
		if ($this->m_currentPos >= 
			$this->m_countResults)
			return FALSE;
		// advance and return result
		$this->m_currentPos++;
		return mysql_fetch_row($this->m_resultSet);
	}
	
	/**
	 *  @brief Opens a database connection.
	 *  
	 *  @return true if succesful otherwise false
	 */
	protected function open()
	{
		$this->close();
		$this->m_connection = mysql_pconnect($this->m_hostname,
			$this->m_user, 
			$this->m_password);
		if (FALSE == $this->m_connection)
			return FALSE;
		// select the database
		if (mysql_select_db($this->m_databaseName,
			$this->m_connection) == FALSE)
			return FALSE;
		return $this->m_connection != FALSE;
	}
	
	/**
	 *  @brief Terminates a database connection and does 
	 *  thenecessary cleanup.
	 *  @endbrief
	 *  
	 *  @return Nothing
	 */
	protected function close()
	{
		// free result set
		if (FALSE != $this->m_resultSet)
		{
			mysql_free_result($this->m_resultSet);
			$this->m_resultSet = FALSE;
		}
		// initialize result set values
		$this->m_countResults = 0;
		$this->m_currentPosResultSet = 0;
		// close connection handle
		if (FALSE != $this->m_connection)
		{
			mysql_close($this->m_connection);
			$this->m_connection = FALSE;
		}
	}

	private $m_hostname; // hostname ip of the database
	private $m_databaseName; // database name
	private $m_user; // database user
	private $m_password; // user password
	private $m_connection; //connection handle FALSE if no connection
	private $m_resultSet;  // result set from a query
	private $m_countResults; // number of records in a result set
	private $m_currentPosResults; // current position in a result set

}
?>