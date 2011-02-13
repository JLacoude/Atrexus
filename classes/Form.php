<?php
/**
 * Form class
 * Used to manage forms displayed
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Form{
  /**
   * @var ISessionManager Instance of a class which implements ISessionManager
   */
  private $_sessionManager;

  /**
   * @var array Contains infos about current form
   */
  private $_currentForm = array();

  /**
   * @var array Available filed types and their corresponding HTML types
   */
  private $_fieldTypes;

  /**
   * Constructor
   *
   * @param object $DI Instance of an IDependencyInjectionContainer
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_sessionManager = $DI->getSessionManager();
    // Clean session
    $config = $DI->getConfigurator();
    $this->cleanFormTokens($config->get('formToken.TTL', 30) * 60);
    // Initialize field types list
    $this->_fieldTypes = array('text' => 'text',
			      'email' => 'text',
			      'password' => 'password',
			      'hidden' => 'hidden',
			      'honeypot' => 'hidden',
			      'submit' => 'submit',
			      'select' => 'select');
  }

  /**
   * Starts a new form. Ends any form started earlier.
   *
   * @param string $action Action parameter of the form
   * @param string $method Method of the form, default is POST
   * @param bool $multipart Used for forms which upload files
   */
  public function start($action = '', $method = 'POST', $multipart = false){
    if(!empty($this->_currentForm)){
      // A form is already open, close it
      $this->end();
    }
    // Display form tag
    echo '
<form action="'.$action.'" method="'.$method.'"';
    if($multipart){
      echo ' enctype="multipart/form-data"';
    }
    echo '>';
    // Generate a token
    $openTokens = $this->_sessionManager->get('formTokens');
    do{
      $tokenId = (string)mt_rand();
    }while(isset($openTokens[$tokenId]));
    $openTokens[$tokenId] = array('startedAt' => time());
    $this->_sessionManager->set('formTokens', $openTokens);

    $this->_currentForm = array('id' => $tokenId,
			       'fields' => array());
    echo'
<input type="hidden" name="token" value="'.$tokenId.'"/>';
  }
  
  /**
   * Ends a form and saves its data in session
   */
  public function end(){
    echo'
</form>';
    $openTokens = $this->_sessionManager->get('formTokens');
    $openTokens[$this->_currentForm['id']]['fields'] = $this->_currentForm['fields'];
    $this->_sessionManager->set('formTokens', $openTokens);
    $this->_currentForm = array();
  }

  /**
   * Add an input to the form
   *
   * @param string $type Input type, default to text
   * @param string $name Name of the input
   * @param string $id Input's id
   * @param string $value Default value
   * @param string $size Size of the input
   * @param string $maxLength Maximum length
   * @param string $more Other raw text added to its code like class or events
   */
  public function addInput($type = 'text', $name = '', $id = '', $value='', $size = '', $maxLength = '', $more = ''){
    $fieldType = isset($this->_fieldTypes[$type])?$this->_fieldTypes[$type]:'text';
    echo '
<input type="'.$fieldType.'"'.
    (!empty($name)?' name="'.$name.'"':'').
      (!empty($id)?' id="'.$id.'"':'').
      ($value != ''?' value="'.htmlspecialchars($value, ENT_QUOTES, 'UTF-8').'"':'').
      ($size != ''?' size="'.$size.'"':'').
      ($maxLength != ''?' maxlength="'.$maxLength.'"':'').
      (!empty($more)?' '.$more:'').'/>';
    $this->_currentForm['fields'][$name] = array('type' => $type);
  }

  /**
   * Add a select input
   *
   * @param string $name Name of the select
   * @param string $id Select's id
   * @param string $more Other raw text added to its code like class or events
   * @param array $content List of values in the select
   * @param string $idColumn Column from which to take id in the $content array
   * @param string $valueColumn Column from which to take values in the $content array
   * @param string $current Current selected element id
   */
  public function addSelect($name, $id= '', $more='', $content, $idColumn = 'id', $valueColumn = 'value', $current = null){
    echo '
<select'.
    (!empty($name)?' name="'.$name.'"':'').
      (!empty($id)?' id="'.$id.'"':'').
      (!empty($more)?' '.$more:'').'>';
    foreach($content as $item){
      echo '
  <option value="'.$item[$idColumn].'"'.($item[$idColumn] == $current?' selected="selected"':'').'>'.$item[$valueColumn].'</option>';
    }
    echo '
</select>';
    $this->_currentForm['fields'][$name] = array('type' => 'select');
  }

  /**
   * Clears all old form data stored in session
   *
   * @param in $ttl Seconds before a form is deemed too old
   */
  public function cleanFormTokens($ttl){
    if(empty($ttl) || $ttl < 0){
      return;
    }
    $openTokens = $this->_sessionManager->get('formTokens');
    if(empty($openTokens)){
      return;
    }
    $currentTime = time();
    foreach($openTokens as $tokenId => $tokenInfos){
      if($tokenInfos['startedAt'] + $ttl < $currentTime){
	unset($openTokens[$tokenId]);
      }
    }
    $this->_sessionManager->set('formTokens', $openTokens);
  }

  /**
   * Gets all data posted
   *
   * @return array
   */
  public function getPosted($formData = array()){
    // Get posted token
    $formId = filter_input(INPUT_POST, 'token', FILTER_VALIDATE_INT);
    if(empty($formId)){
      return array();
    }
    $forms = $this->_sessionManager->get('formTokens');
    
    // Check form associated with token exists
    if(!isset($forms[$formId])){
      return array();
    }
    // Filter all data, depending on forum infos
    $formFields = $forms[$formId]['fields'];
    foreach($formFields as $name => $field){
      if(isset($this->_fieldTypes[$field['type']])){
	$data = $this->{'_checkField'.ucfirst($this->_fieldTypes[$field['type']])}($name);
	if($data !== false && $data !== null){
	  $formData[$name] = $data;
	}
      }
    }
    return $formData;
  }

  /**
   * Get and filter a text field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldText($name){
    return filter_input(INPUT_POST, $name);
  }

  /**
   * Get and filter a select
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldSelect($name){
    return filter_input(INPUT_POST, $name, FILTER_VALIDATE_INT);
  }

  /**
   * Get and filter an email field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldEmail($name){
    return filter_input(INPUT_POST, $name, FILTER_VALIDATE_EMAIL);
  }

  /**
   * Get and filter a password field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldPassword($name){
    return filter_input(INPUT_POST, $name);
  }

  /**
   * Get and filter a hidden field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldHidden($name){
    return filter_input(INPUT_POST, $name);
  }

  /**
   * Get and filter a honeypot field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldHoneypot($name){
    return filter_input(INPUT_POST, $name);
  }

  /**
   * Get and filter a submit field
   *
   * @param string $name Name of the field
   *
   * return string
   */
  private function _checkFieldSubmit($name){
    return filter_input(INPUT_POST, $name);
  }
}