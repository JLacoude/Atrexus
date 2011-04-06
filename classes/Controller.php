<?php
/**
 * Basic controller class.
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Controller{
  /**
   * Action selected by the website user.
   *
   * @var string
   */
  protected $_action;

  /**
   * A User instance.
   *
   * @var object
   */
  protected $_user;

  /**
   * A Config instance.
   *
   * @var object
   */
  protected $_config;

  /**
   * An ILanguage instance.
   *
   * @var object
   */
  protected $_lang;

  /**
   * An IMessenger instance.
   *
   * @var object
   */
  protected $_messenger;

  /**
   * A Form instance.
   *
   * @var object
   */
  protected $_form;

  /**
   * An IDependencyInjectionContainer instance.
   *
   * @var object
   */
  protected $_DI;

  /**
   * Constructor, initialize the member vars. Then call the action found in the query string.
   *
   * @param object An instance of an IDependencyInjectionContainer object.
   *
   * @access public
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_DI = $DI;
    // Load user
    $this->_user = new User($DI);
    // Register configuration
    $this->_config = $DI->getConfigurator();
    // Get language configuration
    $this->_lang = $DI->getLanguage(get_called_class());
    // Get message manager
    $this->_messenger = $DI->getMessenger();
    // Get form helper
    $this->_form = new Form($DI);
  }

  /**
   * Execute controller's specified action
   */
  public function execute(){
    // Get action
    $action = StringTools::noPathFilterInput(INPUT_GET, 'action');
    if(empty($action) || !method_exists($this, $action)){
      if(method_exists($this, 'index')){
	$action = 'index';
      }
      else{
	return;
      }
    }
    $this->_action = $action;
    // Call the action's method
    $this->{$action}();
  }

  /**
   * Loads a template to display the current page.
   *
   * @access public
   */
  public function loadTemplate(){
    $config = $this->_config;
    $lang = $this->_lang;
    $messages = $this->_messenger->get();
    $this->_messenger->flush();
    $user = $this->_user;
    $form = $this->_form;
    include __DIR__.'/../templates/header.tpl';
    include __DIR__.'/../templates/'.get_called_class().'/'.$this->_action.'.tpl';
    include __DIR__.'/../templates/footer.tpl';
  }
}