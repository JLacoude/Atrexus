<?php
/**
 * Database driven class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class DatabaseDriven{
  /**
   * @var object PDO instance
   * @access protected
   */
  protected $_db;

  /**
   * @var object ISqlRequestManager instance
   * @access protected
   */
  protected $_requests;

  /**
   * @var object IMessenger instance
   * @access protected
   */
  protected $_messenger;

  /**
   * @var object ILanguage instance
   * @access protected
   */
  protected $_lang;

  /**
   * @desc Class contructor
   * @param object IDependencyInjectionContainer instance
   * @access public
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_db = $DI->getDb();
    $this->_requests = $DI->getSqlQueriesManager();
    $this->_messenger = $DI->getMessenger();
    $this->_lang = $DI->getLanguage(get_called_class());
  }
}
