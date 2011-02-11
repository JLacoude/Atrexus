<?php
/**
 * QueriesManager
 * Manages SQL queries used by the app
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
require __DIR__.'/../interfaces/ISqlQueriesManager.php';

class SqlQueriesManager implements ISqlQueriesManager{
  /**
   * $requests
   * Array storing all the available queries
   *
   * @var array
   * @access private
   */
  private $requests = array();

  /**
   * Constructor
   * Loads a file which should be located in ../sql/$sqlDriver_requests.ini
   *
   * @param string $sqlDriver name of a SQL driver to use
   */
  public function __construct($sqlDriver){
    $sqlDriver = preg_replace('`[^a-zA-Z0-9_-]`', '', $sqlDriver);
    $filePath = __DIR__.'/../sql/'.$sqlDriver.'_requests.ini';
    if(empty($sqlDriver) || !file_exists($filePath)){
      throw(new Exception('Sql requests file not found'));
    }
    $this->requests = parse_ini_file($filePath);
  }
  
  /**
   * Get a SQL query string
   *
   * @param string $queryId Id of the query
   * @return string
   */
  public function get($queryId){
    if(!isset($this->requests[$queryId])){
      throw(new Exception('Request not found'));
    }
    return $this->requests[$queryId];
  }
}