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
}