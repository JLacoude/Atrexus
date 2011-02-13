<?php
/**
 * Language interface
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
interface ILanguage{
  /**
   * Construtor
   *
   * @param string $languageCode Language code to load. Default to en
   */
  public function __construct($languageCode = 'en');

  /**
   * Loads the language data for a controller
   *
   * @param string $controller Controller name
   */
  public function load($controller);

  /**
   * Get a string
   *
   * @param string $string String Id
   * @param string $default Default value
   */
  public function get($string, $default = '');
}