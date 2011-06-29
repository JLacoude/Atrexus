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
}