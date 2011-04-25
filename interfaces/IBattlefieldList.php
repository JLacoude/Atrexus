<?php
/**
 * Battlefield list interface
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IBattlefieldList{
  /**
   * Get a list of available battlefield for a user
   *
   * @param object $user Instance of a User object
   *
   * @return array List of battlefields available
   */
  public function getListForUser(User $user);
}