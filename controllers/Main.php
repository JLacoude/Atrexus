<?php
/**
 * Main controller to display default page.
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Main extends Controller{
  /**
   * Index page.
   */
  public function index(){
  }

  /**
   * Logs user out
   */
  public function logout(){
    $this->_user->logout();
    Url::redirect(Url::generate(''));
  }

  /**
   * Used to display login form
   */
  public function showLoginForm(){
    $this->timeBeforeNextLogin = $this->_user->timeBeforeNextLogin();
  }

  /**
   * Login user
   */
  public function login(){
    $redirectTo = Url::generate('Main', 'showLoginForm', '&');
    if($this->_user->isRegistered()){
      $redirectTo = Url::generate('');
    }
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else if($this->_user->loginByUserPass($posted['login'], $posted['password'])){
      $redirectTo = Url::generate('Play');
    }
    $this->_messenger->saveInSession();
    Url::redirect($redirectTo);
  }
}