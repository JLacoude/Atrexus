<?php
/**
 * Dependency manager
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class DependencyInjectionContainer implements IDependencyInjectionContainer{
  /**
   * List of object instances
   *
   * @var array
   */
  private $_instances = array();

  /**
   * Instance of Config class
   *
   * @var object
   */
  private $_config;

  /**
   * Constructor
   *
   * @param string $pathToIniFile Path to the application's ini file
   */
  public function __construct($pathToIniFile){
    $this->_config = new Config($pathToIniFile);
  }

  /**
   * Returns an ExtendedPDO object connected to the database configurated
   *
   * @return object
   */
  public function getDb(){
    if(!isset($this->_instances['db']) || !is_a($this->_instances['db'], 'ExtendedPDO')){
      $this->_instances['db'] = new ExtendedPDO($this->_config->get('db.driver').':host='.
						$this->_config->get('db.host').';port='.
						$this->_config->get('db.port').';dbname='.
						$this->_config->get('db.database'),
						$this->_config->get('db.user'),
						$this->_config->get('db.pass'),
						array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES "UTF8"'));
      $this->_instances['db']->setAttribute(PDO::ATTR_ERRMODE, 
					    PDO::ERRMODE_EXCEPTION);
    }
    return $this->_instances['db'];
  }

  /**
   * Returns a SessionManager object
   *
   * @return object
   */
  public function getSessionManager(){
    if(!isset($this->_instances['session']) || !is_a($this->_instances['session'], 'ISessionManager')){
      $this->_instances['session'] = new Session();
    }
    return $this->_instances['session'];
  }

  /**
   * Returns a SqlQueriesManager object
   *
   * @return object
   */
  public function getSqlQueriesManager(){
    if(!isset($this->_instances['SqlQueriesManager']) || !is_a($this->_instances['SqlQueriesManager'], 'SqlQueriesManager')){
      $this->_instances['SqlQueriesManager'] = new SqlQueriesManager($this->_config->get('db.driver'));
    }
    return $this->_instances['SqlQueriesManager'];
  }

  /**
   * Returns a Language object
   *
   * @param string $controller Controller name for which to load a language file
   *
   * @return object
   */
  public function getLanguage($controller){
    $lang = new Language();
    $lang->load($controller);
    return $lang;
  }

  /**
   * Returns a Messenger object
   *
   * @return object
   */
  public function getMessenger(){
    if(!isset($this->_instances['messenger']) || !is_a($this->_instances['messenger'], 'IMessenger')){
      $this->_instances['messenger'] = new Messenger($this->getSessionManager());
    }
    return $this->_instances['messenger'];
  }

  /**
   * Returns a Config object
   *
   * @return object
   */
  public function getConfigurator(){
    return $this->_config;
  }
}