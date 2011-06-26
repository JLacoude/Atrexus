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
   *
   * @return bool true if the target as been killed, false otherwise
   */
  public function receiveDamage($damages){
    if($damages >= $this->HP){
      // Soldier is dead, destroy position
      $this->_db->executeRequest('deletePosition', array(':id' => $this->position_id));
      return true;
    }
    else{
      $this->_db->executeRequest('updateSoldierHP', array(':id' => $this->ID,
							  ':damages' => $damages));
      return false;
    }
  }

  /**
   * Updates soldier's AP if needed
   */
  public function updateAP(){
    $apGain = $this->_ruleset->get('soldier.apGain');
    $period = $this->_ruleset->get('game.period');
    $maxAp = $this->_ruleset->get('soldier.maxAp');
    $updatedAP = APHelpers::update($this->_ruleset->get('soldier.maxAp'),
				   $this->_ruleset->get('game.period'),
				   $this->_ruleset->get('soldier.apGain'),
				   $this->AP,
				   $this->time_from_last_regen);
    if($updatedAP['ap'] != $this->AP){
      $this->_db->executeRequest('updateSoldierAP', array(':id' => $this->ID,
							  ':ap' => $updatedAP['ap'],
							  ':toRemove' => $updatedAP['toRemove']));
      $this->AP = $updatedAP['ap'];
    }
  }
}