<?php
/**
 * Action class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Action implements IDisplayable{
  /**
   * @var array Stores item data
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
   * @param string $actionType Type of action available
   * @param object $DI Instance of IDependencyInjectionContainer object
   */
  public function __construct($actionType, IDependencyInjectionContainer $DI){
    $this->targetType = StringTools::filterPath($actionType);
    // Get language configuration
    $this->_lang = $DI->getLanguage('Action');
    // Get form helper
    $this->_form = new Form($DI);
  }

  /**
   * Displays an action
   *
   * @param string $context Context to display it in. 
   */
  public function display($context = 'board'){
    $context = StringTools::filterPath($context);
    $templateFilePath = __DIR__.'/../templates/Actions/'.($this->targetType).'/'.$context.'.tpl';
    if(file_exists($templateFilePath)){
      include $templateFilePath;
    }
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
}