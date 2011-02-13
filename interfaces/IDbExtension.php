<?php
/**
 * IDbExtension
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IDbExtension{
  /**
   * Returns if a Db transaction has been started
   *
   * @return bool
   */
  public function transactionStarted();
}
