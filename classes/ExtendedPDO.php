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
}
