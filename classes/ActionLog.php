<?php
/**
 * ActionLog class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class ActionLog implements IDisplayable{
  /**
   * @var array Current log data
   * @access private
   */
  private $_data = array();

  /**
   * @var object An ILanguage instance.
   * @access private
   */
  private $_lang;
  /**
   * Constructor
   *
   * @param oject $DI Instance of IDependencyInjectionContainer
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_lang = $DI->getLanguage('ActionLog');
  }

  /**
   * Displays an action
   *
   * @param string $context Context to display it in.
   */
  public function display($context = 'boardloglist'){
    $context = StringTools::filterPath($context);
    $templateFilePath = __DIR__.'/../templates/ActionLog/'.($this->type).'/'.$context.'.tpl';
    if(file_exists($templateFilePath)){
      include $templateFilePath;
    }
  }

  /**
   * Loads a log infos from an array
   *
   * @param array $datas Log datas
   */
  public function loadFromArray($datas){
    if(empty($datas['login'])){
      $datas['login'] = sprintf($this->_lang->get('anonymousUser'), $datas['user_id']);
    }
    $this->_data = $datas;
  }

  /**
   * Set an item info
   *
   * @param string $key Key of the info
   * @param mixed $value Info to store
   */
  public function __set($key, $value){
    if($key == '_data'){
      $this->_data = $value;
    }
    else{
      $this->_data[$key] = $value;
    }
  }

  /**
   * Get magic function to get item infos
   *
   * @param string $key Key name of the value to get
   */
  public function __get($key){
    if($key == '_data'){
      return $this->_data;
    }
    return isset($this->_data[$key])?$this->_data[$key]:null;
  }

  /**
   * Returns if a property exists
   *
   * @param string $key Key name of the value to check
   */
  public function __isset($key){
    if($key == '_data'){
      return true;
    }
    return isset($this->_data[$key]);
  }

}