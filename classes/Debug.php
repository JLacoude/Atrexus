<?php
/**
 * Debug
 * Debug class, used to handle errors and exceptions
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Debug{
  /**
   * Add a line to the message log
   *
   * @param string $message Message to log
   */
  public static function add($message){
    error_log(date('Y-m-d h:i:s : ').$message, 3, __DIR__.'/../debug/messages_'.date('Y-m-d').'.txt');
  }

  /**
   * Exception handler
   * Clear all buffers then load the unexpected exception template
   * Logs the exception in the exception file
   *
   * @param object Exception
   */
  public static function exceptionHandler($e){
    $platform = getenv('platform');
    // Log exception
    $line = date('Y-m-d h:i:s - ').
      'In '.$e->getFile().
      ' at line '.$e->getLine().
      ' exception : '.$e->getMessage().
      '. Trace : '.$e->getTraceAsString().'
';
    error_log($line, 3, __DIR__.'/../debug/exceptions_'.date('Y-m-d').'.txt');

    // Clear buffers
    BufferTools::cleanOutputBuffers();

    // Display error
    $platform = getenv('platform');
    include(__DIR__.'/../templates/exception.tpl');

    // End all operations
    die();
  }

  /**
   * Error handler
   * 
   * @param int $level Level of error
   * @param string $message Error message
   * @param string $file File where the error happened
   * @param int $line Line of the error
   */
  public static function errorHandler($level, $message, $file, $line){
    $levelName = 'error';
    switch($level){
    case E_WARNING:
    case E_USER_WARNING:
      $levelName = 'warning';
      break;
    case E_NOTICE:
    case E_USER_NOTICE:
    case E_DEPRECATED:
    case E_USER_DEPRECATED:
    case E_STRICT:
      $levelName = 'notice';
      break;
    default:
      break;
    }
    // Log the error
    $log = date('Y-m-d h:i:s - ').
      'In '.$file.
      ' at line '.$line.
      ' '.$levelName.' : '.$message.
      '. Trace : '.print_r(debug_backtrace(), true).'

';
    error_log($log, 3, __DIR__.'/../debug/errors_'.date('Y-m-d').'.txt');

    $platform = getenv('platform');
    if($platform == 'dev'){
      if($levelName == 'error'){
	die($log);
      }
      echo '<p>'.$log.'</p>';
    }
    else if($levelName == 'error'){
      // Clear buffers
      BufferTools::cleanOutputBuffers();

      // Display error message
      include(__DIR__.'/../templates/error.tpl');

      // Stop everything
      die();
    }
    return true;
  }
}