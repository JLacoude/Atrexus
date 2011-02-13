<?php
/**
 * Language class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Language implements ILanguage{
  /**
   * @var array Array of strings
   */
  private $_lines = array();

  /**
   * @var string Language code
   */
  private $_languageCode;

  /**
   * Construtor
   *
   * @param string $languageCode Language code to load. Default to en
   */
  public function __construct($languageCode = 'en'){
    $this->_languageCode = $languageCode;
    $this->load('Language');
  }

  /**
   * Loads the language data for a controller
   *
   * @param string $controller Controller name
   */
  public function load($controller){
    $controller = StringTools::filterPath($controller);
    $path = __DIR__.'/../lang/'.$this->_languageCode.'/'.$controller.'.lang.php';
    if(!file_exists($path)){
      throw(new Exception('Language file not found ('.$path.')'));
    }
    include $path;
    $this->_lines += $lines;
  }

  /**
   * Get a string
   *
   * @param string $string String Id
   * @param string $default Default value
   */
  public function get($string, $default = ''){
    return isset($this->_lines[$string])?$this->_lines[$string]:$default;
  }
}
