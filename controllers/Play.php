<?php
/**
 * Main game controller
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Play extends Controller{
  /**
   * Index page.
   */
  public function index(){
    if(!$this->_user->isPlaying()){
      Url::redirect(Url::generate('Battlefields'));
    }
    $this->viewData = $this->_user->getView();
    $this->ruleset = $this->_user->getRuleset();
    $this->personna = $this->_user->getPersonnaData();
  }

  /**
   * Enter a battlefield
   */
  public function enterBattlefield(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    $posted['hiveId'] = isset($posted['hiveId'])?$posted['hiveId']:null;
    if(empty($posted) || empty($posted['id'])){
      // Redirect to battlefield choice page
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
      $urlToRedirect = Url::generate('Battlefields');
    }
    else if(!$this->_user->enterBattlefield($posted['id'], $posted['hiveId'])){
      $urlToRedirect = Url::generate('Battlefields');
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }

  /**
   * Create a soldier on the current battlefield
   */
  public function createSoldier(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    if(empty($posted) || !isset($posted['X'], $posted['Y'])){
      // Logs an error message
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $this->_user->createSoldier($posted['X'], $posted['Y']);
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }

  /**
   * Binds current personna to a new position
   */
  public function bindTo(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    if(empty($posted) || !isset($posted['positionId'])){
      // Logs an error message
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $this->_user->bindTo($posted['positionId']);
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }

  /**
   * Moves a soldier
   */
  public function moveSoldier(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    if(empty($posted) || !isset($posted['X'], $posted['Y'])){
      // Logs an error message
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $this->_user->moveSoldier($posted['X'], $posted['Y']);
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }

  /**
   * Attack a soldier
   */
  public function attackSoldier(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    if(empty($posted) || !isset($posted['soldierId'])){
      // Logs an error message
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $this->_user->attackSoldier($posted['soldierId']);
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }

  /**
   * Capture a headquarter
   */
  public function captureHeadquarter(){
    $urlToRedirect = Url::generate('Play');
    $posted = $this->_form->getPosted();
    if(empty($posted) || !isset($posted['headquarterId'])){
      // Logs an error message
      $this->_messenger->add('error', $this->_lang->get('invalidForm'));
    }
    else{
      $this->_user->captureHeadquarter($posted['headquarterId']);
    }
    $this->_messenger->saveInSession();
    Url::redirect($urlToRedirect);
  }
}