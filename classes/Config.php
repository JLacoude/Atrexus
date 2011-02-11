<?php
/**
 * Config
 * Used to load .ini configuration files
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Config{
  /**
   * Array storing the configuration data.
   *
   * @var array
   *
   * @access private
   */
  private $configuration = array();

  /**
   * Constructor.
   *
   * @param string Full path to the .ini file to load.
   *
   * @throw Exception If file can not be parsed.
   *
   * @access public
   */
  public function __construct($pathToIniFile){
    if(!file_exists($pathToIniFile)){
      throw(new Exception('Configuration file not found'));
    }
    $conf = @parse_ini_file($pathToIniFile);
    if($conf === false){
      throw(new Exception('Configuration file erroneous'));
    }
    $this->configuration = $conf;
  }

  /**
   * Returns an item from the configuration.
   *
   * @param string Key of the item to return.
   * @param default Default value returned if the item is not set.
   *
   * @return string
   *
   * @access public
   */
  public function get($key, $default = null){
    if(!isset($this->configuration[$key])){
      if($default == null){
	return null;
      }
      $this->configuration[$key] = $default;
    }
    return $this->configuration[$key];
  }

  /**
   * Returns an array containing all the configuration data.
   *
   * @return array
   *
   * @access public
   */
  public function getAll(){
    return $this->configuration;
  }
}