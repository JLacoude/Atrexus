<?php
/**
 * StringTools
 * Defines methods used to deal with strings
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class StringTools{
  /**
   * Wrapper for the filter_input php function which filter out any special character
   * so it can be used to specify paths
   *
   * @param int $type Type of input (see filter_input doc)
   * @param string $var Variable name (see filter_input doc)
   * @param int $filter Filter to use first (see filter_input doc)
   * @param array $options Filter options (see filter_input doc)
   *
   * @return string
   */
  public static function noPathFilterInput($type, $var, $filter = FILTER_DEFAULT, $options = null){
    $data = filter_input($type, $var, $filter, $options);
    if(!empty($data)){
      $data = $this->filterPath($data);
    }
    return $data;
  }

  /**
   * Filter a string so it can be used in a path
   *
   * @param string $path 
   *
   * @return string
   */
  public static function filterPath($path){
    return preg_replace('`[^a-zA-Z0-9_-]`', '', $path);
  }

  /**
   * Get a hash from password's hash
   *
   * @param string $password Password to hash
   *
   * @return string
   */
  public static function getHash($password){
    $phpass = new PasswordHash(8, false);
    $hash = $phpass->HashPassword($password);
    return $hash;
  }

  /**
   * Checks if a hash validates a password
   *
   * @param string $hashed Hash used to check
   * @param string $clear The password to check
   *
   * @return bool
   */
  public static function checkHash($hashed, $clear){
    $phpass = new PasswordHash(8, true);
    return $phpass->CheckPassword($clear, $hashed);
  }
}