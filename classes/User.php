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
      $timeBeforeNextLogin = $this->timeBeforeNextLogin();
      $this->_addAttempt();
      if($timeBeforeNextLogin > 0){
	$this->_messenger->add('error', sprintf($this->_lang->get('tooManyAttempts'), floor($timeBeforeNextLogin/60), $timeBeforeNextLogin%60));
      }
      else{
	$userInfos = $this->_db->fetchFirstRequest('getUserByLogin', array(':login' => $login));
	if(!empty($userInfos)){
	  $hasher = new PasswordHash(8, false);
	  if($hasher->checkPassword($pass, $userInfos['password'])){
	    $this->_userInfos = $userInfos;
	    $this->_sessionStoreUserId(true);
	    $logged = true;
	    $this->_resetAttempts();
	  }
	  else{
	    $this->_messenger->add('error', $this->_lang->get('wrongPassword'));
	  }
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('loginNotFound'));
	}
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
      $userInfos = $this->_db->fetchFirstRequest('getUserByLogin', array(':login' => $login));
      if(empty($userInfos)){
	$hasher = new PasswordHash(8, false);
	$hashedPassword = $hasher->hashPassword($password);
	if(!empty($hashedPassword)){
	  $stmt = $this->_db->executeRequest('registerUser', array(':login' => $login,
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
      $this->_userInfos = $this->_db->fetchFirstRequest('getUserByIP', array(':ip' => $clientIp));
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
      $this->_userInfos = $this->_db->fetchFirstRequest('getUser', array(':id' => $userId));
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
      $id = $this->_db->executeCreateRequest('createNewUser', array(':ip' => $userIP));
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
   *
   * @param bool $deleteData specify if the old session data have to be erased, false by default
   */
  private function _sessionStoreUserId($deleteData = false){
    $this->_sessionManager->newId($deleteData);
    $this->_sessionManager->set('userId', $this->_userInfos['ID']);
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
      $personna = $this->_db->fetchFirstRequest('getUserPersonnaInBattlefield', array(':userId' => $this->_userInfos['ID'],
										      ':battlefieldId' => $battlefieldId));
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
   * Used to get a user out of a battlefield
   */
  public function exitBattlefield(){
    $this->_personna = null;
    $this->_sessionManager->set('personnaId', 0);
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

  /**
   * Binds current personna to a new position
   *
   * @param int $positionId ID of the position to bind to
   */
  public function bindTo($positionId){
    if(!$this->isPlaying()){
      $this->_messenger->add('error', $this->_lang->get('notInGame'));
    }
    $this->_personna->bindTo($positionId);
  }

  /**
   * Moves a soldier on current battlefield
   *
   * @param int $X X coordinate of the new position
   * @param int $Y Y coordinate of the new position
   */
  public function moveSoldier($X, $Y){
    if(!$this->isPlaying()){
      $this->_messenger->add('error', $this->_lang->get('notInGame'));
    }
    $this->_personna->moveSoldier($X, $Y);
  }

  /**
   * Make current controlled soldier attack an ennemy soldier
   *
   * @param int $soldierId Id of the soldier to attack
   */
  public function attackSoldier($soldierId){
    if(!$this->isPlaying()){
      $this->_messenger->add('error', $this->_lang->get('notInGame'));
    }
    $this->_personna->attackSoldier($soldierId);
  }

  /**
   * Capture a headquarter
   *
   * @param int $headquarterId ID of the headquarter to capture
   */
  public function captureHeadquarter($headquarterId){
    if(!$this->isPlaying()){
      $this->_messenger->add('error', $this->_lang->get('notInGame'));
    }
    $this->_personna->captureHeadquarter($headquarterId);
  }

  /**
   * Change user's password
   *
   * @param string $currentPassword Current user password to check if it can be changed
   * @param string $newPassword New password
   */
  public function changePassword($currentPassword, $newPassword){
    $this->_db->beginTransaction();
    try{
      $userInfos = $this->_db->fetchFirstRequest('getUserPassword', array(':id' => $this->_userInfos['ID']));
      if(!empty($userInfos)){
	$hasher = new PasswordHash(8, false);
	if($hasher->checkPassword($currentPassword, $userInfos['password'])){
	  $hashedPassword = $hasher->hashPassword($newPassword);
	  if(!empty($hashedPassword)){
	    $this->_db->executeRequest('changeUserPassword', array(':password' => $hashedPassword,
								   ':id' => $this->_userInfos['ID']));
	  }
	  else{
	    $this->_messenger->add('error', $this->_lang->get('passwordMiscError'));
	  }
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('wrongPassword'));
	}
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
   * Change user's email
   *
   * @param string $currentPassword Current user password to check if it can be changed
   * @param string $newEmail New Email
   */
  public function changeEmail($currentPassword, $newEmail){
    $this->_db->beginTransaction();
    try{
      $userInfos = $this->_db->fetchFirstRequest('getUserPassword', array(':id' => $this->_userInfos['ID']));
      if(!empty($userInfos)){
	$hasher = new PasswordHash(8, false);
	if($hasher->checkPassword($currentPassword, $userInfos['password'])){
	  $this->_db->executeRequest('changeUserEmail', array(':email' => $newEmail,
							      ':id' => $this->_userInfos['ID']));
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('wrongPassword'));
	}
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
   * Returns the number of seconds until next login attempt is available
   *
   * @return int number of seconds until next attempt
   */
  public function timeBeforeNextLogin(){
    $clientIp = $this->_getClientIp();
    $loginTriesInfos = $this->_db->fetchFirstRequest('getLoginTries', array(':ip' => $clientIp));
    if(empty($loginTriesInfos) || 
	    $loginTriesInfos['counter'] < 5 ||
	    $loginTriesInfos['seconds_from_last'] > pow(10, $loginTriesInfos['counter'] - 4)){
      return 0;
    }
    return  pow(10, $loginTriesInfos['counter'] - 4) - $loginTriesInfos['seconds_from_last'];
  }

  /**
   * Updates user login attempts
   */
  private function _addAttempt(){
    $clientIp = $this->_getClientIp();
    $this->_db->executeRequest('addLoginAttempt', array(':ip' => $clientIp));
  }

  /**
   * Resets a user attempts counter
   */
  private function _resetAttempts(){
    $clientIp = $this->_getClientIp();
    $this->_db->executeRequest('resetLoginAttempt', array(':ip' => $clientIp));
  }

  /**
   * Creates a reset password token for an account identified either by a login or an email.
   *
   * @param string $login User login
   * @param string $meail User email
   *
   * @return boolean
   * @todo replace the call to mail() by a proper mailer class
   */
  public function createResetToken($login, $email){
    $result = false;
    $this->_db->beginTransaction();
    try{
      if(!empty($login)){
	$account = $this->_db->fetchFirstRequest('getUserByLogin', array(':login' => $login));
	if(!empty($account['email'])){
	  $email = $account['email'];
	}
      }

      if(empty($email)){
	if(empty($account)){
	  $this->_messenger->add('error', $this->_lang->get('loginNotFound'));
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('accountWithoutEmail'));
	}
	return false;
      }
      else{
	if(empty($account)){
	  $account = $this->_db->fetchFirstRequest('getUserByEmail', array(':email' => $email));
	}
	if(empty($account)){
	  $this->_messenger->add('error', $this->_lang->get('emailNotFound'));
	}
	else{
	  do{
	    $token = StringTools::generateToken();
	    $alreadyExists = $this->_db->fetchFirstRequest('getPasswordResetByToken', array(':token' => $token));
	  }while(!empty($alreadyExists));
	  $this->_db->executeRequest('createPasswordReset', array(':token' => $token, ':userId' => $account['ID']));
	  $to = str_replace(array("\r", "\n", "%0a", "%0d"), '', $email);
	  $subject = $this->_lang->get('resetMailTitle');
	  $body = sprintf($this->_lang->get('resetMailBody'), $account['login'], Url::generate('Account', 'showPasswordSetForm', '&', array('token' => $token)));
	  if(mail($to, $subject, $body, 'From: Atrexus <noreply@atrexus.com>'."\n\r")){
	    $result = true;
	  }
	  else{
	    $this->_messenger->add('error', $body);
	    $this->_messenger->add('error', $this->_lang->get('emailNotSent'));
	  }
	}
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
    return $result;
  }

  /**
   * Checks if a token to reset password exists and is not too old
   *
   * @param string $token Token to check
   * @return bool
   */
  public function checkToken($token){
    $exists = false;
    $this->_db->beginTransaction();
    try{
      $tokenData = $this->_db->fetchFirstRequest('getPasswordResetByToken', array(':token' => $token));
      if(!empty($tokenData)){
	$exists = true;
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
    return $exists;
  }

  /**
   * Set a new password to an user account
   *
   * @param string $token Password token used to reset the account
   * @param string $password New password
   * @return bool
   */
  public function setNewPassword($token, $password){
    $changed = false;
    $this->_db->beginTransaction();
    try{
      $tokenData = $this->_db->fetchFirstRequest('getPasswordResetByToken', array(':token' => $token));
      if(!empty($tokenData)){
	$hasher = new PasswordHash(8, false);
	$hashedPassword = $hasher->hashPassword($password);
	if(!empty($hashedPassword)){
	  $this->_db->executeRequest('changeUserPassword', array(':password' => $hashedPassword,
								 ':id' => $tokenData['user_id']));
	  $this->_db->executeRequest('cleanTokens', array(':userId' => $tokenData['user_id']));
	  $changed = true;
	}
	else{
	  $this->_messenger->add('error', $this->_lang->get('passwordMiscError'));
	}
      }
    }
    catch(Exception $e){
      $this->_db->rollBack();
      throw($e);
    }
    $this->_db->commit();
    return $changed;
  }
}