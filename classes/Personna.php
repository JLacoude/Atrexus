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
	  $view[$item['X']][$item['Y']]->isEnnemy = $this->_data['hive_id'] != $item['hq_hive'];
	}
	else{
	  $view[$item['X']][$item['Y']] = new Soldier($item['soldier_id'], $this->_DI);
	  // Copy values relevant to soldiers into the object
	  foreach(array('X', 'Y', 'hive_id', 'HP', 'AP', 'updated') as $key){
	    $view[$item['X']][$item['Y']]->{$key} = $item[$key];
	  }
	  $view[$item['X']][$item['Y']]->isEnnemy = $this->_data['hive_id'] != $item['hive_id'];
	}
	$view[$item['X']][$item['Y']]->positionId = $item['position_id'];
	$view[$item['X']][$item['Y']]->isCurrent = ($item['X'] == $this->_data['X'] && $item['Y'] == $this->_data['Y']);
      }

      // Set actions cells where available
      $around = array(array(-1, 1),
		      array(0, 1),
		      array(1, 1),
		      array(-1, 0),
		      array(1, 0),
		      array(-1, -1),
		      array(0, -1),
		      array(1, -1));
      if($view[$this->_data['X']][$this->_data['Y']] instanceof Headquarter){
	$actionType = 'headquarter';
	$cost = $this->_ruleset->get('soldier.apCost');
      } 
      else{
	$actionType = 'soldier';
	$cost = $this->_ruleset->get('soldier.apPerMvt');
      }
      foreach($around as $offset){
	$x = $this->_data['X'] + $offset[0];
	$y = $this->_data['Y'] + $offset[1];
	if(!isset($view[$x][$y])){
	  $view[$x][$y] = new ActionCell(0, $this->_DI);
	  $view[$x][$y]->type = $actionType;
	  $view[$x][$y]->cost =  $cost;
	  $view[$x][$y]->availableAP = $this->_data['AP'];
	  $view[$x][$y]->X =  $x;
	  $view[$x][$y]->Y =  $y;
	}
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

  /**
   * Creates a soldier on current battlefield
   *
   * @param int $X X coordinate of the soldier to create
   * @param int $Y Y coordinate of the soldier to create
   */
  public function createSoldier($X, $Y){
    $createAp = $this->_ruleset->get('soldier.apCost');
    $this->_db->beginTransaction();
    try{
      // Get personna AP and position
      $sql = $this->_requests->get('getPersonnaHeadquarter');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id' => $this->_data['ID']));
      $personna = $stmt->fetch();
      if(empty($personna)){
	$this->_messenger->add('error', $this->_lang->get('noPersonna'));
      }
      else if(empty($personna['headquarter_id'])){
	$this->_messenger->add('error', $this->_lang->get('notAtHQ'));
      }
      else if($personna['AP'] < $createAp){
	$this->_messenger->add('error', sprintf($this->_lang->get('notEnoughAP'), $this->_data['AP'], $createAp));
      }
      else if(abs($personna['X'] - $X) > 1 || 
	      abs($personna['Y'] - $Y) > 1){
	$this->_messenger->add('error', $this->_lang->get('coordinatesTooFar'));
      }
      else{
	// Checks if the targeted cell is free
	$sql = $this->_requests->get('getPositionByCoordinates');
	$stmt = $this->_db->prepare($sql);
	$stmt->execute(array(':X' => $X, ':Y' => $Y, ':battlefield' => $this->_data['battlefield_id']));
	$position = $stmt->fetch();
	if(!empty($position)){
	  $this->_messenger->add('error', $this->_lang->get('cellNotEmpty'));
	}
	else{
	  // Creates the soldier's position
	  $sql = $this->_requests->get('createPosition');
	  $stmt = $this->_db->prepare($sql);
	  $stmt->execute(array(':X' => $X, ':Y' => $Y, ':battlefield' => $this->_data['battlefield_id']));
	  $positionId = $this->_db->lastInsertId();
	  // Create the soldier
	  $sql = $this->_requests->get('createSoldier');
	  $stmt = $this->_db->prepare($sql);
	  $stmt->execute(array(':hive' => $this->_data['hive_id'],
			       ':position' => $positionId,
			       ':HP' => $this->_ruleset->get('soldier.maxHP'),
			       ':AP' => $this->_ruleset->get('soldier.maxAp')));
	  // Update personna
	  $sql = $this->_requests->get('personnaUseAP');
	  $stmt = $this->_db->prepare($sql);
	  $stmt->execute(array(':ap' => $createAp, ':id' => $this->_data['ID']));
	}
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
  }

  /**
   * Binds current personna to a new position
   *
   * @param int $positionId ID of the position to bind to
   */
  public function bindTo($positionId){
    $this->_db->beginTransaction();
    try{
      // Check if position can be used
      $distance = $this->_ruleset->get('game.viewDistance');
      $sql = $this->_requests->get('getHivePosition');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id' => $positionId));
      $position = $stmt->fetch();
      if(empty($position)){
	$this->_messenger->add('error', $this->_lang->get('positionNotFound'));
      }
      else if($position['hive_id'] != $this->_data['hive_id']){
	$this->_messenger->add('error', $this->_lang->get('isEnnemyPosition'));
      }
      else if($position['battlefield_id'] != $this->_data['battlefield_id']){
	$this->_messenger->add('error', $this->_lang->get('wrongBattlefield'));
      }
      else if(abs($position['X'] - $this->_data['X']) > $distance ||
	      abs($position['Y'] - $this->_data['Y']) > $distance){
	$this->_messenger->add('error', $this->_lang->get('positionTooFar'));
      }
      else{
	$sql = $this->_requests->get('bindTo');
	$stmt = $this->_db->prepare($sql);
	$stmt->execute(array(':position' => $positionId, ':id' => $this->_data['ID']));
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
  }
}