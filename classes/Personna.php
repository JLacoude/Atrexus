<?php
/**
 * Personna class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Personna extends DatabaseDriven implements IPersonna{
  /**
   * @var array personna data
   * @access private
   */
  private $_data = array();

  /**
   * @var object Instance of IConfig object storing the rulesets
   * @access private
   */
  private $_ruleset;

  /**
   * Constructor. Loads ruleset and call parents constructor
   *
   * @param object $DI Instance of IdependencyInjectionContainer
   */
  public function __construct($DI){
      $this->_ruleset = new Config(__DIR__.'/../config/defaultRuleset.ini');
      parent::__construct($DI);
  }

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
      $maxAP = $this->_ruleset->get('personna.maxAp');
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
      $apGain = $this->_ruleset->get('personna.apGain');
      $period = $this->_ruleset->get('game.period');
      $maxAp = $this->_ruleset->get('personna.maxAp');
      // New AP value
      $ap = $personna['AP'] + $apGain / $period * $personna['time_from_last_regen'];
      $ap = max(0, min($maxAp, $ap));
      // If new AP < 100 we may have some seconds to remove from the time of last_regen
      $secondsToRemove = 0;
      if($ap < $maxAp){
	$difference = ceil($ap) - $ap;
	$secondsToRemove = $difference * $period / $apGain;
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

  /**
   * Returns an array which contains everything in view of the personna
   *
   * @return array
   */
  public function getView(){
    $this->_db->beginTransaction();
    try{
      $view = array();
      $maxView = $this->_ruleset->get('game.viewDistance');
      $sql = $this->_requests->get('getView');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':battlefield' => $this->_data['battlefield_id'],
			   ':x' => $this->_data['X'],
			   ':y' => $this->_data['Y'],
			   ':distance' => $maxView));
      $result = $stmt->fetchAll();
      foreach($result as $item){
	if(!isset($view[$item['X']])){
	  $view[$item['X']] = array();
	}
	if(!empty($item['hq_id'])){
	  $view[$item['X']][$item['Y']] = new Headquarter($item['hq_id'], $this->_DI);
	}
	else{
	  $view[$item['X']][$item['Y']] = new Soldier($item['soldier_id'], $this->_DI);
	  // Copy values relevant to soldiers into the object
	  foreach(array('X', 'Y', 'hive_id', 'HP', 'AP', 'updated') as $key){
	    $view[$item['X']][$item['Y']]->{$key} = $item[$key];
	  }
	}
	$view[$item['X']][$item['Y']]->isEnnemy = $this->_data['hive_id'] != $item['hive_id'];
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
    return $view;
  }

  /**
   * Returns ruleset of personna's battlefield
   *
   * @return array
   */
  public function getRuleset(){
    return $this->_ruleset->getAll();
  }

  /**
   * Returns datas about the current user personna
   *
   * @return array
   */
  public function getData(){
    return $this->_data;
  }

}