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
	$sql = $this->_requests->get('getBattlefieldListForUser');
      }
      else{
	$sql = $this->_requests->get('getBattlefieldListForAnonUser');
      }
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':userId' => $userId));
      $list = $stmt->fetchAll();
      foreach($list as $i => $battlefield){
	$sql = $this->_requests->get('getBattlefieldHiveList');
	$stmt = $this->_db->prepare($sql);
	$stmt->execute(array(':battlefieldId' => $battlefield['ID']));
	$list[$i]['hiveList'] = $stmt->fetchAll();	
      }
    }
    catch(Exception $e){
      throw $e;
    }
    return $list;
  }
}