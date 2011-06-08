<?php
/**
 * Battlefield list class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class BattlefieldList extends DatabaseDriven implements IBattlefieldList{
  /**
   * Get a list of available battlefield for a user
   *
   * @param object $user Instance of a User object
   *
   * @return array List of battlefields available
   */
  public function getListForUser(User $user){
    $userId = $user->ID;
    try{
      if($user->isRegistered()){
	$list = $this->_db->fetchAllRequest('getBattlefieldListForUser', array(':userId' => $userId));
      }
      else{
	$list = $this->_db->fetchAllRequest('getBattlefieldListForAnonUser', array(':userId' => $userId));
      }
      foreach($list as $i => $battlefield){
	$list[$i]['hiveList'] = $this->_db->fetchAllRequest('getBattlefieldHiveList', array(':battlefieldId' => $battlefield['ID']));	
      }
    }
    catch(Exception $e){
      throw $e;
    }
    return $list;
  }
}