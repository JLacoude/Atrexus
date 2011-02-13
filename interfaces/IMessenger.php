<?php
/**
 * Messenger interface
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IMessenger{
  /**
   * Constructor.
   *
   * @param object $session An ISessionManager.
   */
  public function __construct(ISessionManager $session);

  /**
   * Add a message to the message list.
   *
   * @param string $class   Message class. Used to group messages (like "error" or "warning").
   * @param string $message The message string.
   */
  public function add($class, $message);

  /**
   * Saves the message list in session.
   */
  public function saveInSession();

  /**
   * Get the message list.
   *
   * @param string $class Message class to limit the type of message returned.
   */
  public function get($class = null);

  /**
   * Empty the message list and empty the messages saved in session.
   */
  public function flush($class = null);
}