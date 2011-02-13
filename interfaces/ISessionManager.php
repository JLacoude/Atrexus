<?php
/**
 * Session interface
 * Used to manage sessions
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface ISessionManager{
  /**
   * Set a value in session
   *
   * @param string $var Name of the variable to set
   * @param mixed $value Value to set it to
   */
  public function set($var, $value);

  /**
   * Get a value stored in session
   *
   * @param string $var Name of the variable to get
   * @param mixed $default Default value to get, defaults to null
   *
   * @return mixed
   */
  public function get($var, $default = null);

  /**
   * Regenerate a session Id
   *
   * @param bool $deleteOld Specify if the session's data are to be destroyed. Default false
   *
   * @return string New session Id
   */
  public function newId($deleteOld = false);

  /**
   * Wipes a session
   */
  public function destroy();
}