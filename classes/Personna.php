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
      $stmt = $this->_db->fetchFirstRequest('getRandomHeadquarter', array(':hiveId' => $hiveId,
									  ':battlefieldId' => $battlefieldId));
      // Get ruleset data
      $maxAP = $this->_ruleset->get('personna.maxAp');
      // Create a personna for this battlefield
      $personnaId = $this->_db->executeCreateRequest('createPersonna', array(':userId' => $userId,
									     ':hiveId' => $hiveId,
									     ':positionId' => $headquarter['ID'],
									     ':AP' => $maxAP));
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
      $personna = $this->_db->fetchFirstRequest('getPersonna', array(':id' => $id));
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
	$this->_db->executeRequest('updatePersonna', array(':id' => $id,
							   ':ap' => $ap,
							   ':toRemove' => $secondsToRemove));
	$personna['AP'] = $ap;
      }
      $personna['logs'] = array();
      $logs = $this->_db->fetchAllRequest('getPersonnaLogs', array(':userId' => $personna['user_id'],
								   ':battlefieldId' => $personna['battlefield_id'],
								   ':currentItemId' => $personna['current_item_id']));
      if(!empty($logs)){
	foreach($logs as $log){
	  $actionLog = new ActionLog($this->_DI);
	  $actionLog->loadFromArray($log);
	  $personna['logs'][] = $actionLog;
	}
      }
      if($personna['is_soldier']){
	$personna['item'] = new Soldier($personna['current_item_id'], $this->_DI);
	$personna['item']->setRuleset($this->_ruleset);
      }
      else{
	$personna['item'] = new Headquarters($personna['current_item_id'], $this->_DI);
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
      $result = $this->_db->fetchAllRequest('getView', array(':battlefield' => $this->_data['battlefield_id'],
							     ':x' => $this->_data['X'],
							     ':y' => $this->_data['Y'],
							     ':distance' => $maxView));
      foreach($result as $item){
	$distance = max(abs($item['X'] - $this->_data['X']), abs($item['Y'] - $this->_data['Y']));
	if(!isset($view[$item['X']])){
	  $view[$item['X']] = array();
	}
	if(!empty($item['hq_id'])){
	  $gameItem = new Headquarter($item['hq_id'], $this->_DI);
	  $gameItem->costToCapture = $item['cost_to_capture'];
	  $gameItem->isEnnemy = $this->_data['hive_id'] != $item['hq_hive'];
	}
	else{
	  $gameItem = new Soldier($item['soldier_id'], $this->_DI, true);
	  // Copy values relevant to soldiers into the object
	  foreach(array('X', 'Y', 'hive_id', 'HP', 'AP', 'updated') as $key){
	    $gameItem->{$key} = $item[$key];
	  }
	  $gameItem->isEnnemy = $this->_data['hive_id'] != $item['hive_id'];
	}
	$gameItem->isCurrent = $distance == 0;
	// Add "bindTo" action to items from same hive
	if(!$gameItem->isEnnemy && !$gameItem->isCurrent){
	  $action = new Action('bindToItem', $this->_DI);
	  $action->positionId = $item['position_id'];
	  $gameItem->actions[] = $action;
	}
	if($this->_data['is_soldier'] &&
	   $distance == 1 && 
	   $gameItem->isEnnemy){
	  // Add "attackSoldier" action to ennemy soldier in range
	  $attackCost = $this->_ruleset->get('soldier.apPerAttack');
	  if($this->_data['AP'] >= $attackCost &&
	     $this->_data['soldier_AP'] >= $attackCost &&
	     $gameItem instanceof Soldier){
	    $action = new Action('attackSoldier', $this->_DI);
	    $action->soldierId = $item['soldier_id'];
	    $gameItem->actions[] = $action;
	  }
	  // Add "captureHeadquarter" action
	  if($gameItem instanceof Headquarter &&
	     $this->_data['AP'] >= $gameItem->costToCapture &&
	     $this->_data['soldier_AP'] >= $gameItem->costToCapture){
	    $action = new Action('captureHeadquarter', $this->_DI);
	    $action->headquarterId = $item['hq_id'];
	    $gameItem->actions[] = $action;
	  }
	}
	$view[$item['X']][$item['Y']] = $gameItem;
      }
      // Set actions cells where available
      if($this->_data['is_soldier']){
	$cost = $this->_ruleset->get('soldier.apPerMvt');
	$actionType = 'moveSoldier';
      }
      else{
	$cost = $this->_ruleset->get('soldier.apCost');
	$actionType = 'createSoldier';
      }
      if($this->_data['AP'] >= $cost &&
	 (!$this->_data['is_soldier'] || $this->_data['soldier_AP'] >= $cost)){
	$around = array(array(-1, 1),
			array(0, 1),
			array(1, 1),
			array(-1, 0),
			array(1, 0),
			array(-1, -1),
			array(0, -1),
			array(1, -1));
	foreach($around as $offset){
	  $x = $this->_data['X'] + $offset[0];
	  $y = $this->_data['Y'] + $offset[1];
	  if(!isset($view[$x][$y])){
	    $actionCell = new ActionCell(0, $this->_DI);
	    $action = new Action($actionType, $this->_DI);
	    $action->X = $x;
	    $action->Y = $y;
	    $action->cost = $cost;
	    $actionCell->actions[] = $action;
	    $view[$x][$y] = $actionCell;
	  }
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
      $personna = $this->_db->fetchFirstRequest('getPersonnaHeadquarter', array(':id' => $this->_data['ID']));      
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
	if($this->_checkCoordinate($X, $Y)){
	  $this->_messenger->add('error', $this->_lang->get('cellNotEmpty'));
	}
	else{
	  // Creates the soldier's position
	  $positionId = $this->_db->executeCreateRequest('createPosition', array(':X' => $X, 
										 ':Y' => $Y, 
										 ':battlefield' => $this->_data['battlefield_id']));
	  // Create the soldier
	  $soldierId = $this->_db->executeCreateRequest('createSoldier', array(':hive' => $this->_data['hive_id'],
									       ':position' => $positionId,
									       ':HP' => $this->_ruleset->get('soldier.maxHP'),
									       ':AP' => $this->_ruleset->get('soldier.maxAp')));
	  // Update personna
	  $this->_db->executeRequest('personnaUseAP', array(':ap' => $createAp, ':id' => $this->_data['ID']));
	  // Create log
	  $this->_db->executeRequest('createSoldierLog', array(':user_id' => $this->_data['user_id'],
							       ':battlefield_id' => $personna['battlefield_id'],
							       ':by_id' => $personna['headquarter_id'],
							       ':by_X' => $personna['X'],
							       ':by_Y' => $personna['Y'],
							       ':target_id' => $soldierId,
							       ':target_X' => $X,
							       ':target_Y' => $Y));
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
      $position = $this->_db->fetchFirstRequest('getHivePosition', array(':id' => $positionId));
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
	$this->_db->executeRequest('bindTo', array(':position' => $positionId, ':id' => $this->_data['ID']));
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
  }

  /**
   * Moves a soldier on current battlefield
   *
   * @param int $X X coordinate of the new position
   * @param int $Y Y coordinate of the new position
   */
  public function moveSoldier($X, $Y){
    $this->_db->beginTransaction();
    try{
      if($this->_checkCoordinate($X, $Y)){
	$this->_messenger->add('error', $this->_lang->get('cellNotEmpty'));
      }
      else{
	$moveAp = $this->_ruleset->get('soldier.apPerMvt');
	// Get personna AP and position
	$personna = $this->_db->fetchFirstRequest('getPersonnaSoldier', array(':id' => $this->_data['ID']));
	if(empty($personna)){
	  $this->_messenger->add('error', $this->_lang->get('noPersonna'));
	}
	else if(empty($personna['soldier_id'])){
	  $this->_messenger->add('error', $this->_lang->get('notASoldier'));
	}
	else if($personna['AP'] < $moveAp ||
		$personna['soldier_ap'] < $moveAp){
	  $this->_messenger->add('error', sprintf($this->_lang->get('notEnoughAP'), $this->_data['AP'] . ' - ' . $personna['soldier_ap'], $moveAp));
	}
	else if(abs($personna['X'] - $X) > 1 || 
		abs($personna['Y'] - $Y) > 1){
	  $this->_messenger->add('error', $this->_lang->get('coordinatesTooFar'));
	}
	else{
	  $this->_db->executeRequest('movePosition', array(':id' => $personna['position_id'],
							   ':x' => $X,
							   ':y' => $Y));
	  $this->_db->executeRequest('soldierUseAP', array(':id' => $personna['soldier_id'],
							   ':ap' => $moveAp));
	  $this->_db->executeRequest('personnaUseAP', array(':id' => $this->_data['ID'],
							    ':ap' => $moveAp));
	  // Create log
	  $this->_db->executeRequest('moveSoldierLog', array(':user_id' => $this->_data['user_id'],
							     ':battlefield_id' => $personna['battlefield_id'],
							     ':target_id' => $personna['soldier_id'],
							     ':target_X' => $X,
							     ':target_Y' => $Y));
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
   * Make current controlled soldier attack an ennemy soldier
   *
   * @param int $soldierId Id of the soldier to attack
   */
  public function attackSoldier($soldierId){
    $this->_db->beginTransaction();
    try{
      $attackAp = $this->_ruleset->get('soldier.apPerAttack');
      $personna = $this->_db->fetchFirstRequest('getPersonnaSoldier', array(':id' => $this->_data['ID']));
      if(empty($personna)){
	$this->_messenger->add('error', $this->_lang->get('noPersonna'));
      }
      else if(empty($personna['soldier_id'])){
	$this->_messenger->add('error', $this->_lang->get('notASoldier'));
      }
      else if($personna['AP'] < $attackAp ||
	      $personna['soldier_ap'] < $attackAp){
	$this->_messenger->add('error', sprintf($this->_lang->get('notEnoughAP'), $personna['AP'] . ' - ' . $personna['soldier_ap'], $attackAp));
      }
      else{
	$target = new Soldier($soldierId, $this->_DI);
	if(empty($target->ID)){
	  $this->_messenger->add('error', $this->_lang->get('soldierNotFound'));
	}
	else if($personna['battlefield_id'] != $target->battlefield_id ||
		abs($personna['X'] - $target->X) > 1 ||
		abs($personna['Y'] - $target->Y) > 1){
	  $this->_messenger->add('error', $this->_lang->get('targetTooFar'));
	}
	else if($personna['hive_id'] == $target->hive_id){
	  $this->_messenger->add('error', $this->_lang->get('invalidTarget'));
	}
	else{
	  $damages = $this->_ruleset->get('soldier.damages');
	  $killed = $target->receiveDamage($damages);
	  $this->_db->executeRequest('soldierUseAP', array(':id' => $personna['soldier_id'],
							   ':ap' => $attackAp));
	  $this->_db->executeRequest('personnaUseAP', array(':id' => $this->_data['ID'],
							    ':ap' => $attackAp));
	  // Create log
	  $this->_db->executeRequest('attackSoldierLog', array(':user_id' => $this->_data['user_id'],
							       ':battlefield_id' => $personna['battlefield_id'],
							       ':by_id' => $personna['soldier_id'],
							       ':by_X' => $personna['X'],
							       ':by_Y' => $personna['Y'],
							       ':target_id' => $soldierId,
							       ':target_X' => $target->X,
							       ':target_Y' => $target->Y,
							       ':damages' => $damages,
							       ':kill' => $killed));
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
   * Capture a headquarter
   *
   * @param int $headquarterId ID of the headquarter to capture
   */
  public function captureHeadquarter($headquarterId){
    $this->_db->beginTransaction();
    try{
      $personna = $this->_db->fetchFirstRequest('getPersonnaSoldier', array(':id' => $this->_data['ID']));
      if(empty($personna)){
	$this->_messenger->add('error', $this->_lang->get('noPersonna'));
      }
      else if(empty($personna['soldier_id'])){
	$this->_messenger->add('error', $this->_lang->get('notASoldier'));
      }
      else{
	$target = $this->_db->fetchFirstRequest('getHeadquarter', array(':id' => $headquarterId));
	if(empty($target)){
	  $this->_messenger->add('error', $this->_lang->get('headquarterNotFound'));
	}
	else if($personna['AP'] < $target['cost_to_capture'] ||
		$personna['soldier_ap'] < $target['cost_to_capture']){
	  $this->_messenger->add('error', sprintf($this->_lang->get('notEnoughAP'), 
						  $personna['AP'] . ' - ' . $personna['soldier_ap'], 
						  $target['cost_to_capture']));
	}
	else if($personna['battlefield_id'] != $target['battlefield_id'] ||
		abs($personna['X'] - $target['X']) > 1 ||
		abs($personna['Y'] - $target['Y']) > 1){
	  $this->_messenger->add('error', $this->_lang->get('targetTooFar'));
	}
	else if($personna['hive_id'] == $target['hive_id']){
	  $this->_messenger->add('error', $this->_lang->get('invalidTarget'));
	}
	else{
	  $this->_db->executeRequest('updateHeadquarterHive', array(':id' => $headquarterId,
								    ':hive' => $personna['hive_id']));
	  $this->_db->executeRequest('soldierUseAP', array(':id' => $personna['soldier_id'],
							   ':ap' => $target['cost_to_capture']));
	  $this->_db->executeRequest('personnaUseAP', array(':id' => $this->_data['ID'],
							    ':ap' => $target['cost_to_capture']));
	  $this->_db->executeRequest('captureHeadquarterLog', array(':user_id' => $this->_data['user_id'],
								    ':battlefield_id' => $personna['battlefield_id'],
								    ':by_id' => $personna['soldier_id'],
								    ':by_X' => $personna['X'],
								    ':by_Y' => $personna['Y'],
								    ':target_id' => $headquarterId,
								    ':target_X' => $target['X'],
								    ':target_Y' => $target['Y']));
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
   * Checks if something is already at some coordinates of current battlefield
   *
   * @param int $X X coordinate
   * @param int $Y Y coordinate
   *
   * @return bool true if something is there
   */
  private function _checkCoordinate($X, $Y){
    $position = $this->_db->fetchFirstRequest('getPositionByCoordinates', array(':X' => $X, 
										':Y' => $Y, 
										':battlefield' => $this->_data['battlefield_id']));
    return !empty($position);
  }
}