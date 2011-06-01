<?php
/**
 * User class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class User extends DatabaseDriven{
  /**
   * @var object ISessionManager instance
   * @access private
   */
  private $_sessionManager;

  /**
   * @var array User infos
   * @access private
   */
  private $_userInfos;

  /**
   * @var object Instance of a IPersonna object
   * @access private
   */
  private $_personna;

  /**
   * @desc Class contructor
   * @param object IDependencyInjectionContainer instance
   * @access public
   */
  public function __construct(IDependencyInjectionContainer $DI){
    parent::__construct($DI);
    $this->_personna = new Personna($DI);
    $this->_sessionManager = $DI->getSessionManager();
    $this->identify();
  }

  /**
   * Returns if current user is a registered one
   *
   * @return bool
   */
  public function isRegistered(){
    return !empty($this->_userInfos['login']);
  }

  /**
   * Returns if current user is playing
   *
   * @return bool
   */
  public function isPlaying(){
    return $this->_personna->inGame();
  }

  /**
   * Returns the objects visible to a user in game
   *
   * @return array
   */
  public function getView(){
    return $this->_personna->getView();
  }

  /**
   * Returns ruleset of personna's battlefield
   *
   * @return array
   */
  public function getRuleset(){
    return $this->_personna->getRuleset();
  }

  /**
   * Returns datas about the current user personna
   *
   * @return array
   */
  public function getPersonnaData(){
    return $this->_personna->getData();
  }

  /**
   * Identify a user
   *
   * @return int User ID
   */
  public function identify(){
    $this->_loginBySession();
    if(empty($this->_userInfos)){
      $this->_loginWithIp();
    }

    if(empty($this->_userInfos)){
      throw(new Exception('Could not identify user'));
    }
  }

  /**
   * Log a user using a username and a password stored by this app
   *
   * @param string $login User login
   * @param string $pass User password
   */
  public function loginByUserPass($login, $pass){
    $logged = false;
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('getUserByLogin');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':login' => $login));
      $userInfos = $stmt->fetch();
      if(!empty($userInfos)){
	$hasher = new PasswordHash(8, false);
	if($hasher->checkPassword($pass, $userInfos['password'])){
	    $this->_userInfos = $userInfos;
	    $this->_sessionStoreUserId();
	    $logged = true;
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('wrongPassword'));
	}
      }
      else{
	$this->_messenger->add('error', $this->_lang->get('loginNotFound'));
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw $e;
    }
    $this->_db->commit();
    return $logged;
  }

  /**
   * Logs out a registered user
   */
  public function logout(){
    if($this->isRegistered()){
      $this->_sessionManager->destroy();
    }
    $this->_sessionManager->newId();
  }

  /**
   * Registers a new user
   *
   * @param string $login User's login
   * @param string $password User's password
   * @param string $email User's email
   *
   * @return boolean
   */
  public function register($login, $password, $email){
    if($this->isRegistered()){
      $this->_messenger->add('error', $this->_lang->get('alreadyRegistered'));
      return false;
    }

    $this->_db->beginTransaction();
    $registered = false;
    try{
      $sql = $this->_requests->get('getUserByLogin');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':login' => $login));
      $userInfos = $stmt->fetch();
      if(empty($userInfos)){
	$hasher = new PasswordHash(8, false);
	$hashedPassword = $hasher->hashPassword($password);
	if(!empty($hashedPassword)){
	  $sql = $this->_requests->get('registerUser');
	  $stmt = $this->_db->prepare($sql);
	  $stmt->execute(array(':login' => $login,
			       ':password' => $hashedPassword,
			       ':email' => $email,
			       ':id' => $this->_userInfos['ID']));
	  if($stmt->rowCount() <= 0){
	    $this->_messenger->add('error', $this->_lang->get('registerUserError'));
	  }
	  else{
	    $this->_userInfos['login'] = $login;
	    $this->_userInfos['email'] = $email;
	    $registered = true;
	  }
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('passwordMiscError'));
	}
      }
      else{
	$this->_messenger->add('error', $this->_lang->get('loginInUse'));
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw $e;
    }
    $this->_db->commit();
    return $registered;
  }

  /**
   * Use the client IP to identify him
   */
  private function _loginWithIp(){
    $clientIp = $this->_getClientIp();
    // Check if we already have a user with this IP in database
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('getUserByIP');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':ip' => $clientIp));
      $this->_userInfos = $stmt->fetch();
      if(empty($this->_userInfos)){
	$userId = $this->_createUser($clientIp);
	$this->_userInfos = array('ID' => $userId,
				  'IP' => $clientIp);
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw $e;
    }
    $this->_db->commit();
    $this->_sessionStoreUserId();
  }

  /**
   * If user has an account in session, loads infos
   */
  private function _loginBySession(){
    $userId = $this->_sessionManager->get('userId');
    if(empty($userId)){
      return;
    }
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('getUser');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':id' => $userId));
      $this->_userInfos = $stmt->fetch();
      
      $personnaId = $this->_sessionManager->get('personnaId');
      if(!empty($personnaId)){
	$this->_personna->load($personnaId);
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw $e;
    }
    $this->_db->commit();
  }

  /**
   * Creates a new user in database
   *
   * @param string $userIP User IP
   *
   * @return int
   */
  private function _createUser($userIP){
    $id = 0;
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('createNewUser');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':ip' => $userIP));
      $id = $this->_db->lastInsertId();
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw $e;
    }
    $this->_db->commit();
    return $id;
  }

  /**
   * Saves connected user's ID in session
   */
  private function _sessionStoreUserId(){
    $this->_sessionManager->set('userId', $this->_userInfos['ID']);
    $this->_sessionManager->newId();
  }

  /**
   * Get a client's IP
   *
   * @return string
   */
  private function _getClientIp(){
    return $_SERVER['REMOTE_ADDR'];
  }

  /**
   * Get magic function to get user infos
   *
   * @param string $key Key name of the value to get
   */
  public function __get($key){
    return isset($this->_userInfos[$key])?$this->_userInfos[$key]:null;
  }

  /**
   * Used by users to enter battlefields. Creates a new personna if needed.
   *
   * @param int $battlefieldId Id of the chosen battlefield
   * @param int $hiveId Id of a hive if a new personna must be created
   */
  public function enterBattlefield($battlefieldId, $hiveId = null){
    $this->_db->beginTransaction();
    try{
      $sql = $this->_requests->get('getUserPersonnaInBattlefield');
      $stmt = $this->_db->prepare($sql);
      $stmt->execute(array(':userId' => $this->_userInfos['ID'],
			   ':battlefieldId' => $battlefieldId));
      $personna = $stmt->fetch();
      if(empty($personna)){
	$personnaId = $this->_personna->create($this->_userInfos['ID'], $battlefieldId, $hiveId);
	$this->_sessionManager->set('personnaId', $personnaId);
      }
      else{
	$this->_personna->load($personna['ID']);
	$this->_sessionManager->set('personnaId', $personna['ID']);
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
    return true;
  }

  /**
   * Creates a soldier on current battlefield
   *
   * @param int $X X coordinate of the soldier to create
   * @param int $Y Y coordinate of the soldier to create
   */
  public function createSoldier($X, $Y){
    if(!$this->isPlaying()){
      $this->_messenger->add('error', $this->_lang->get('notInGame'));
    }
    $this->_personna->createSoldier($X, $Y);
  }
}