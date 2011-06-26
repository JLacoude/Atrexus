<?php
/**
 * Session class
 * Used to manage sessions
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Session implements ISessionManager{
  /**
   * Constructor
   */
  public function __construct(){
    session_start();
  }

  /**
   * Destructor
   */
  public function __destruct(){
    session_write_close();
  }

  /**
   * Set a value in session
   *
   * @param string $var Name of the variable to set
   * @param mixed $value Value to set it to
   */
  public function set($var, $value){
    $_SESSION[$var] = $value;
  }

  /**
   * Get a value stored in session
   *
   * @param string $var Name of the variable to get
   * @param mixed $default Default value to get, defaults to null
   *
   * @return mixed
   */
  public function get($var, $default = null){
    return isset($_SESSION[$var])?$_SESSION[$var]:$default;
  }

  /**
   * Regenerate a session Id
   *
   * @param bool $deleteOld Specify if the session's data are to be destroyed. Default false
   *
   * @return string New session Id
   */
  public function newId($deleteOld = false){
    if($deleteOld){
      $_SESSION = array();
    }
    return session_regenerate_id($deleteOld);
  }

  /**
   * Wipes a session
   */
  public function destroy(){
    // Empty session data
    $_SESSION = array();
    // Destroy session cookie
    if(ini_get("session.use_cookies")){
      $params = session_get_cookie_params();
      setcookie(session_name(), 
		'', 
		time() - 42000,
		$params["path"], 
		$params["domain"],
		$params["secure"], 
		$params["httponly"]);
    }
    // Destroy session
    session_destroy();
  }
}