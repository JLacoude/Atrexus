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
    $to = str_replace(array("\r", "\n"), '', $to);
    header('Location: '.$to);
    exit;
  }

  /**
   * Generates an Atrexus url
   *
   * @param string $controller Controller to redirect to
   * @param string $action Controller's action to use
   * @param string $separator Query string separator to use
   */
  public static function generate($controller, $action = null, $separator = '&amp;'){
    $host  = $_SERVER['HTTP_HOST'];
    $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    $query = '';
    if(!empty($controller)){
      $query.='?controller='.$controller.(!empty($action)?$separator.'action='.$action:'');
    }
    return 'http://'.$host.$uri.'/index.php'.$query;
  }
}