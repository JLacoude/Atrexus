<?php
/**
 * Account page controller
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Account extends Controller{
  /**
   * Index page.
   */
  public function index(){
    if(!$this->_user->isRegistered()){
      Url::redirect(Url::generate(''));
    }
  }

  /**
   * Change user password
   */
  public function changePassword(){
    if(!$this->_user->isRegistered()){
      Url::redirect(Url::generate(''));
    }
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else if(!isset($posted['password'], $posted['passwordVerif'], $posted['oldPassword'])){
      $this->_messenger->add('error', $this->_lang->get('missingData'));
    }
    else if($posted['passwordVerif'] != $posted['password']){
      $this->_messenger->add('error', $this->_lang->get('passwordMismatch'));
    }
    else if($this->_user->changePassword($posted['oldPassword'], $posted['password'])){
      $this->_messenger->add('success', $this->_lang->get('passwordChanged'));

    }
    $this->_messenger->saveInSession();
    Url::redirect(Url::generate('Account'));
  }

  /**
   * Change user email
   */
  public function changeEmail(){
    if(!$this->_user->isRegistered()){
      Url::redirect(Url::generate(''));
    }
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else if(!isset($posted['email'], $posted['oldPassword'])){
      $this->_messenger->add('error', $this->_lang->get('missingData'));
    }
    else if($this->_user->changeEmail($posted['oldPassword'], $posted['email'])){
      $this->_messenger->add('success', $this->_lang->get('emailChanged'));

    }
    $this->_messenger->saveInSession();
    Url::redirect(Url::generate('Account'));
  }

  /**
   * Displays a reset password form
   */
  public function showResetForm(){
    if($this->_user->isRegistered()){
      Url::redirect(Url::generate('Account'));
    }
  }

  /**
   * Displays the password set form
   */
  public function showPasswordSetForm(){
    if($this->_user->isRegistered()){
      Url::redirect(Url::generate('Account'));
    }
    $token = filter_input(INPUT_GET, 'token');
    if(empty($token) || !$this->_user->checkToken($token)){
      $this->_messenger->add('error', $this->_lang->get('invalidToken'));
      $this->_messenger->saveInSession();
      Url::redirect(Url::generate(''));
    }
    $this->token = StringTools::filterForHTML($token);
  }

  /**
   * Generates a password reset token then sends an email to the user
   */
  public function resetPassword(){
    if($this->_user->isRegistered()){
      Url::redirect(Url::generate('Account'));
    }
    $redirectTo = Url::generate('Account', 'showResetForm', '&');
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $login = isset($posted['login'])?trim($posted['login']):'';
      $email = isset($posted['email'])?trim($posted['email']):'';
      if(empty($login) && empty($email)){
	$this->_messenger->add('error', $this->_lang->get('missingData'));
      }
      else if($this->_user->createResetToken($login, $email)){
	$this->_messenger->add('success', $this->_lang->get('tokenSent'));
	$redirectTo = Url::generate('Main', 'showLoginForm', '&');
      }
    }
    $this->_messenger->saveInSession();
    Url::redirect($redirectTo);
  }

  /**
   * Sets a new password for a user
   */
  public function setNewPassword(){
    if($this->_user->isRegistered()){
      Url::redirect(Url::generate('Account'));
    }
    $redirectTo = Url::generate('Account', 'showResetForm', '&');
    $posted = $this->_form->getPosted();
    if(empty($posted)){
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else if(!isset($posted['password'], $posted['password2'], $posted['passwordToken'])){
      $this->_messenger->add('error', $this->_lang->get('missingData'));
    }
    else if($posted['password'] != $posted['password2']){
      $this->_messenger->add('error', $this->_lang->get('passwordMismatch'));
    }
    else if($this->_user->setNewPassword($posted['passwordToken'], $posted['password'])){
      $this->_messenger->add('success', $this->_lang->get('newPasswordSet'));
      $redirectTo = Url::generate('');
    }
    $this->_messenger->saveInSession();
    Url::redirect($redirectTo);
  }
}