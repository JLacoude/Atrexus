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
   * Start a transaction if none has been started.
   * @return boolean
   */  
  public function beginTransaction(){
    if(!$this->_transactionStarted){
      $this->_transactionStarted = parent::beginTransaction();
    }
    return $this->_transactionStarted;
  }

  /**
   * Commit a transaction.
   * @return boolean
   */
  public function commit(){
    if($this->_transactionStarted){
      $this->_transactionStarted = false;
      return parent::commit();
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
