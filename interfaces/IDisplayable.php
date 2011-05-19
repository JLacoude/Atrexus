<?php
/**
 * Displayable interface
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface IDisplayable{
  /**
   * Displays an object's data
   *
   * @param string $context
   */
  public function display($context);
}