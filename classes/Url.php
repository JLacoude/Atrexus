<?php
/**
 * URL helper
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Url{
  /**
   * Redirects the user to a new page
   *
   * @param string $to URL of the page redirect to
   */
  public static function redirect($to){
    // Filter url given
    $to = str_replace(array("\r", "\n", "%0a", "%0d"), '', $to);
    header('Location: '.$to);
    exit;
  }

  /**
   * Generates an Atrexus url
   *
   * @param string $controller Controller to redirect to
   * @param string $action Controller's action to use
   * @param string $separator Query string separator to use
   * @param string $params List of parameters to add to the query
   */
  public static function generate($controller, $action = null, $separator = '&amp;', $params = array()){
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $query = '';
    if(!empty($controller)){
      $params['controller'] = $controller;
      if(!empty($action)){
	$params['action'] = $action;
      }
      $query.='?'.http_build_query($params, '', $separator);
    }
    return 'http://'.$host.$uri.'/index.php'.$query;
  }
}