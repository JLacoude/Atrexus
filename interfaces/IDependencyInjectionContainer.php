<?php
/**
 * Dependency manager
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IDependencyInjectionContainer{
  /**
   * Constructor
   *
   * @param string $pathToIniFile Path to the application's ini file
   */
  public function __construct($pathToIniFile);

  /**
   * Returns an ExtendedPDO object connected to the database configurated
   *
   * @return object
   */
  public function getDb();

  /**
   * Returns a SessionManager object
   *
   * @return object
   */
  public function getSessionManager();

  /**
   * Returns a RequestManager object
   *
   * @return object
   */
  public function getRequestManager();

  /**
   * Returns a Language object
   *
   * @param string $controller Controller name for which to load a language file
   *
   * @return object
   */
  public function getLanguage($controller);

  /**
   * Returns a Messenger object
   *
   * @return object
   */
  public function getMessenger();

  /**
   * Returns a Config object
   *
   * @return object
   */
  public function getConfigurator();
}
