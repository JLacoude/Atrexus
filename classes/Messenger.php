<?php
/**
 * Messenger class, used to store messages in session.
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Messenger implements IMessenger{
  /**
   * An ISessionManager instance.
   *
   * @var object
   */
  private $_sessionManager;

  /**
   * List of messages.
   *
   * @var array
   */
  private $_messages;

  /**
   * Constructor.
   *
   * @param object $session An ISessionManager.
   */
  public function __construct(ISessionManager $session){
    $this->_sessionManager = $session;
    $this->_messages = $this->_sessionManager->get('messages', array());
  }

  /**
   * Add a message to the message list.
   *
   * @param string $class   Message class. Used to group messages (like "error" or "warning").
   * @param string $message The message string.
   */
  public function add($class, $message){
    if(!isset($this->_messages[$class])){
      $this->_messages[$class] = array();
    }
    $this->_messages[$class][] = $message;
  }

  /**
   * Saves the message list in session.
   */
  public function saveInSession(){
    $this->_sessionManager->set('messages', $this->_messages);
  }

  /**
   * Get the message list.
   *
   * @param string $class Message class to limit the type of message returned.
   */
  public function get($class = null){
    if(empty($class)){
      return $this->_messages;
    }
    return isset($this->_messages[$class])?$this->_messages[$class]:array();
  }

  /**
   * Empty the message list and empty the messages saved in session.
   */
  public function flush($class = null){
    if(empty($class)){
      $this->_messages = array();
    }
    if(isset($this->_messages[$class])){
      $this->_messages[$class] = array();
    }
    $this->saveInSession();
  }
}