<?php
/**
 * User class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class User{
  /**
   * @var object PDO instance
   * @access private
   */
  private $_db;
 
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
   * @var object ISqlRequestManager instance
   * @access private
   */
  private $_requests;

  /**
   * @var object IMessenger instance
   * @access private
   */
  private $_messenger;

  /**
   * @var object ILanguage instance
   * @access private
   */
  private $_lang;

  /**
   * @desc Class contructor
   * @param object IDependencyInjectionContainer instance
   * @access public
   */
  public function __construct(IDependencyInjectionContainer $DI){
    $this->_db = $DI->getDb();
    $this->_sessionManager = $DI->getSessionManager();
    $this->_requests = $DI->getSqlQueriesManager();
    $this->_messenger = $DI->getMessenger();
    $this->_lang = $DI->getLanguage('User');
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
}