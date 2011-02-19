<?php
/**
 * User class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class User{
  /**
   * @var object PDO instance
   * @access private
   */
  private $_db;
 
  /**
   * @var object ISessionManager instance
   * @access private
   */
  private $_sessionManager;

  /**
   * @var array User infos
   * @access private
   */
  private $_userInfos;

  /**
   * @var object ISqlRequestManager instance
   * @access private
   */
  private $_requests;

  /**
   * @var object IMessenger instance
   * @access private
   */
  private $_messenger;

  /**
   * @var object ILanguage instance
   * @access private
   */
  private $_lang;

  /**
   * @desc Class contructor
   * @param object IDependencyInjectionContainer instance
   * @access public
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_db = $DI->getDb();
    $this->_sessionManager = $DI->getSessionManager();
    $this->_requests = $DI->getSqlQueriesManager();
    $this->_messenger = $DI->getMessenger();
    $this->_lang = $DI->getLanguage('User');
  }

  /**
   * Returns if current user is a registered one
   *
   * @return bool
   */
  public function isRegistered(){
    return !empty($this->_userInfos['login']);
  }
}