<?php
/**
 * Soldier class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Soldier extends GameItem{
  /**
   * Constructor
   *
   * @param int $id ID of the item
   * @param object $DI Instance of IDependencyInjectionContainer
   * @param bool $noLoad If set to true, prevents loading of soldier data. Defaults to false
   */
  public function __construct($id, IDependencyInjectionContainer $DI, $noLoad = false){
    parent::__construct($id, $DI);
    if(!$noLoad){
      $data = $this->_db->fetchFirstRequest('getSoldierInfos', array(':id' => $this->ID));
      foreach($data as $key => $value){
	$this->{$key} = $value;
      }
    }
  }

  /**
   * Manage what happens to a soldier when receiving damages
   *
   * @param int $damages Amount of damage received
   */
  public function receiveDamage($damages){
    if($damages >= $this->HP){
      // Soldier is dead, destroy position
      $this->_db->executeRequest('deletePosition', array(':id' => $this->position_id));
    }
    else{
      $this->_db->executeRequest('updateSoldierHP', array(':id' => $this->ID,
							  ':damages' => $damages));
    }
  }
}