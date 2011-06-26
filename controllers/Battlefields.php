<?php
/**
 * Battlefield selection controller
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Battlefields extends Controller{
  /**
   * Index page.
   */
  public function index(){
    if($this->_user->isPlaying()){
      Url::redirect(Url::generate('Play'));
    }
    $battlefieldList = new BattlefieldList($this->_DI);
    $this->availableBattlefields = $battlefieldList->getListForUser($this->_user);
  }

  public function leave(){
    if($this->_user->isPlaying()){
      $this->_user->exitBattlefield();
    }
    Url::redirect(Url::generate('Play'));
  }
}