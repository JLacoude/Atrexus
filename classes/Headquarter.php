<?php
/**
 * Headquarter class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Headquarter extends DatabaseDriven implements IDisplayable{
  /**
   * @var array Stores hq data
   * @access private
   */
  private $_data = array();

  /**
   * An ILanguage instance.
   *
   * @var object
   */
  protected $_lang;

  /**
   * A Form instance.
   *
   * @var object
   */
  protected $_form;

  /**
   * Constructor
   *
   * @param int $id ID fo the soldier
   * @param object $DI Instance of IDependencyInjectionContainer
   */
  public function __construct($id, IDependencyInjectionContainer $DI){
    $this->_data['ID'] = $id;
    // Get language configuration
    $this->_lang = $DI->getLanguage(get_called_class());
    // Get form helper
    $this->_form = new Form($DI);
    parent::__construct($DI);
  }
  
  /**
   * Set a headquarter info
   *
   * @param string $key Key of the info
   * @param mixed $value Info to store
   */
  public function __set($key, $value){
    $this->_data[$key] = $value;
  }

  /**
   * Get magic function to get headquarter infos
   *
   * @param string $key Key name of the value to get
   */
  public function __get($key){
    return isset($this->_data[$key])?$this->_data[$key]:null;
  }

  /**
   * Returns if a property exists
   *
   * @param string $key Key name of the value to check
   */
  public function __isset($key){
    return isset($this->_data[$key]);
  }

  /**
   * Displays a headquarter's infos
   *
   * @param string $context Context to display it in. 
   */
  public function display($context = 'board'){
    if(in_array($context, array('board'))){
      include __DIR__.'/../templates/Headquarter/'.$context.'.tpl';
    }
  }
}