<?php
/**
 * ISqlQueriesManager
 * Interface of SQL queries manager
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface ISqlQueriesManager{
  /**
   * Constructor
   * 
   * @param string $sqlDriver name of SQL driver in use
   */
  public function __construct($sqlDriver);

  /**
   * Gets a SQL query string
   *
   * @param string $queryId Id of the query to get
   */
  public function get($queryId);
}
