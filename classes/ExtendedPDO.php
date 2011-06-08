<?php
/**
 * Extended PDO class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class ExtendedPDO extends PDO implements IDbExtension{
  /**
   * Specify if a transaction has already been started.
   * @var boolean
   */
  private $_transactionStarted = false;

  /**
   * Level of transaction
   * @var integer
   */
  private $_transactionLevel = 0;

  /**
   * @var object ISqlQueriesManager instance
   * @access protected
   */
  private $_requests;

  /**
   * Start a transaction if none has been started.
   * @return boolean
   */  
  public function beginTransaction(){
    if(!$this->_transactionStarted){
      $this->_transactionStarted = parent::beginTransaction();
    }
    if($this->_transactionStarted){
      $this->_transactionLevel++;
    }
    return $this->_transactionStarted;
  }

  /**
   * Commit a transaction.
   * @return boolean
   */
  public function commit(){
    if($this->_transactionStarted){
      $this->_transactionLevel--;
      if($this->_transactionLevel <= 0){
	$this->_transactionStarted = !parent::commit();
	return !$this->_transactionStarted;
      }
    }
    return true;
  }

  /**
   * Rollback a transaction.
   * @return boolean
   */
  public function rollBack(){
    if($this->_transactionStarted){
      $this->_transactionStarted = false;
      $this->_transactionLevel = 0;
      return parent::rollBack();
    }
    return true;
  }

  /**
   * Return if a transation has been started or not.
   * @return boolean
   */
  public function transactionStarted(){
    return $this->_transactionStarted;
  }

  /**
   * Execute a request and returns a PDO statement
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @return PDOStatement
   */
  public function executeRequest($requestName, $parameters = null){
    $sql = $this->_requests->get($requestName);
    $stmt = $this->prepare($sql);
    $stmt->execute($parameters);
    return $stmt;
  }

  /**
   * Execute a request and returns the ID of the item created
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @return int ID of the item created if it has an auto-increment field
   */
  public function executeCreateRequest($requestName, $parameters = null){
    $this->executeRequest($requestName, $parameters);
    return $this->lastInsertId();
  }


  /**
   * Execute a request then fetch and return the first result
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @param array Array Result from a select request
   */
  public function fetchFirstRequest($requestName, $parameters = null){
    $stmt = $this->executeRequest($requestName, $parameters);
    return $stmt->fetch();
  }

  /**
   * Execute a request then fetch all results
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @param array Array of results from a select request
   */
  public function fetchAllRequest($requestName, $parameters = null){
    $stmt = $this->executeRequest($requestName, $parameters);
    return $stmt->fetchAll();
  }

  /**
   * Set request manager
   *
   * @var object ISqlRequestManager instance
   */
  public function setRequestManager(ISqlQueriesManager $requests){
    $this->_requests = $requests;
  }
}
