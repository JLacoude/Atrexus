<?php
/**
 * APHelpers class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class APHelpers{
  /**
   * Determines a new AP value and seconds to remove to the last update timer
   *
   * @param int $maxAP Maximum AP
   * @param int $period Time for a game turn
   * @param int $apGain Speed of AP gain
   * @param int $currentAP Current AP
   * @param int $lastRegen Time elapsed from last AP gain
   *
   * @return array Array of the form array('ap' => (int)New AP value,
   *                                       'toRemove' => (int)Seconds to remove from the last gain value)
   */
  public static function update($maxAP, $period, $apGain, $currentAP, $lastRegen){
    // New AP value
    $ap = $currentAP + $apGain / $period * $lastRegen;
    $ap = max(0, min($maxAP, $ap));
    
    // If new AP < maxAP we may have some seconds to remove from the time of last_regen
    $secondsToRemove = 0;
    if($ap < $maxAP){
      $difference = ceil($ap) - $ap;
      $secondsToRemove = $difference * $period / $apGain;
      $ap = floor($ap);
    }
    return array('ap' => $ap,
		 'toRemove' => $secondsToRemove);
  }
}