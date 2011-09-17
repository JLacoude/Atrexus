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

  /**
   * Generate and shows a battlefield global map
   */
  public function showMap(){
    $battlefieldId = filter_input(INPUT_GET, 'battlefieldId', FILTER_SANITIZE_NUMBER_INT);
    $battlefield = new Battlefield($this->_DI, $battlefieldId);
    $battlefieldData = $battlefield->getData();
    $this->picturePath = $battlefield->generateMap();
    $this->battlefieldName = $battlefieldData['name'];
    $this->hives = $battlefieldData['hives'];
  }
}