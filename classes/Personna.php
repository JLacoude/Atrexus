<?php
class Personna extends DatabaseDriven implements IPersonna{
  /**
   * @var array personna data
   * @access private
   */
  private $_data = array();

  /**
   * Creates a personna for a user on a battlefield
   *
   * @param int $userId Id of the user
   * @param int $battlefieldId
   * @param int $hiveId
   *
   * @return int ID of the created personna
   */
  public function create($userId, $battlefieldId, $hiveId){
    $this->_db->beginTransaction();
    try{
      // Get position of a random headquarter
      $sql = $this->_requests->get('getRandomHeadquarter');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':hiveId' => $hiveId,
			   ':battlefieldId' => $battlefieldId));
      $headquarter = $stmt->fetch();
      // Get ruleset data
      $ruleset = new Config(__DIR__.'/../config/defaultRuleset.ini');
      $maxAP = $ruleset->get('personna.maxAp');
      // Create a personna for this battlefield
      $sql = $this->_requests->get('createPersonna');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':userId' => $userId,
			   ':hiveId' => $hiveId,
			   ':positionId' => $headquarter['ID'],
			   ':AP' => $maxAP));
      $personnaId = $this->_db->lastInsertId();
      $this->load($personnaId);
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
  }

  /**
   * Loads a personna infos and update its AP if needed
   *
   * @param int $id ID of the personna to load
   */
  public function load($id){
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('getPersonna');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id' => $id));
      $personna = $stmt->fetch();
      if(empty($personna)){
	throw(new Exception('Personna not found'));
      }
      // Get ruleset data
      $ruleset = new Config(__DIR__.'/../config/defaultRuleset.ini');
      $apGain = $ruleset->get('personna.apGain');
      // New AP value
      $ap = $personna['AP'] + $apGain / 3600 * $personna['time_from_last_regen'];
      $ap = max(0, min(100, $ap));
      // If new AP < 100 we may have some seconds to remove from the time of last_regen
      $secondsToRemove = 0;
      if($ap < 100){
	$difference = ceil($ap) - $ap;
	$secondsToRemove = $difference * 3600 / $apGain;
	$ap = floor($ap);
      }

      if($ap != $personna['AP']){
	$sql = $this->_requests->get('updatePersonna');
	$stmt = $this->_db->prepare($sql);
	$stmt->execute(array(':id' => $id,
			     ':ap' => $ap,
			     ':toRemove' => $secondsToRemove));
	$personna['AP'] = $ap;
      }

      $this->_data = $personna;
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
  }
  /**
   * Returns if currently loaded personna is in game
   *
   * @return bool
   */
  public function inGame(){
    return !empty($this->_data['ID']);
  }
}