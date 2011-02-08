<?php
/**
 * Configurator class. Used to load .ini configuration files.
 *
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
    $conf = parse_ini_file($pathToIniFile);
    if($conf === false){
      throw(new Exception('Configuration file not found'));
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
    return isset($this->configuration[$key])?$this->configuration[$key]:$default;
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