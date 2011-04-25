<?php
/**
 * Personna interface
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IPersonna{
  /**
   * Creates a personna for a user on a battlefield
   *
   * @param int $userId user ID
   * @param int $battlefieldId Battlefield ID
   * @param int $hiveId
   *
   * @return int ID of the created personna
   */
  public function create($userId, $battlefieldId, $hiveId);
}