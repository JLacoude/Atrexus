<?php
/**
 * Register controller
 *
 * Manages the registering process
 */
class Register extends Controller{
  /**
   * Index page
   */
  public function index(){
  }

  public function saveAccount(){
    $urlToRedirect = Url::generate('Register');
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      // Redirect to register page
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else if($posted['password'] != $posted['passwordVerif']){
      $this->_messenger->add('error', $this->_lang->get('passwordMismatch'));
    }
    else if($this->_user->register($posted['login'], $posted['password'], $posted['email'])){
      $urlToRedirect = Url::generate('');
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }
}