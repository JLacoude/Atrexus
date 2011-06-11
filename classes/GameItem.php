<?php
/**
 * GameItem class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */

class GameItem extends DatabaseDriven implements IDisplayable{
  /**
   * @var array Stores item data
   * @access private
   */
  private $_data = array();

  /**
   * @var object Instance of IConfig object storing the rulesets
   * @access private
   */
  private $_ruleset;

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
   * @param int $id ID of the item
   * @param object $DI Instance of IDependencyInjectionContainer
   */
  public function __construct($id, IDependencyInjectionContainer $DI){
    $this->_data['ID'] = $id;
    $this->_data['actions'] = new ArrayObject(array(), ArrayObject::ARRAY_AS_PROPS);
    // Get language configuration
    $this->_lang = $DI->getLanguage(get_called_class());
    // Get form helper
    $this->_form = new Form($DI);
    parent::__construct($DI);
  }
  
  /**
   * Set an item info
   *
   * @param string $key Key of the info
   * @param mixed $value Info to store
   */
  public function __set($key, $value){
    $this->_data[$key] = $value;
  }

  /**
   * Get magic function to get item infos
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
   * Displays an item's infos
   *
   * @param string $context Context to display it in. 
   */
  public function display($context = 'board'){
    $context = StringTools::filterPath($context);
    $templateFilePath = __DIR__.'/../templates/'.get_called_class().'/'.$context.'.tpl';
    if(file_exists($templateFilePath)){
      include $templateFilePath;
    }
  }

  /**
   * Set ruleset
   *
   * @param object $ruleset Instance of Config object
   */
  public function setRuleset(Config $ruleset){
    $this->_ruleset = $ruleset;
  }
}
