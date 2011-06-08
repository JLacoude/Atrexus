<?php
/**
 * IDbExtension
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IDbExtension{
  /**
   * Returns if a Db transaction has been started
   *
   * @return bool
   */
  public function transactionStarted();

  /**
   * Execute a request and returns a PDO statement
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @return PDOStatement
   */
  public function executeRequest($requestName, $parameters = null);

  /**
   * Execute a request and returns the ID of the item created
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @return int ID of the item created if it has an auto-increment field
   */
  public function executeCreateRequest($requestName, $parameters = null);


  /**
   * Execute a request then fetch all results
   *
   * @param string $requestName Name of the request to execute
   * @param array $parameters Parameters of the request. Defaults to null
   *
   * @param array Array of results from a select request
   */
  public function fetchAllRequest($requestName, $parameters = null);

  /**
   * Set request manager
   *
   * @var object ISqlRequestManager instance
   */
  public function setRequestManager(ISqlQueriesManager $requests);
}
