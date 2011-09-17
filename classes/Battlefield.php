<?php
/**
 * Battlefield class
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class Battlefield extends DatabaseDriven{
  /**
   * @var object Instance of IConfig object storing the rulesets
   * @access private
   */
  private $_ruleset;

  /**
   * @var int A battlefield Id
   * @access private
   */
  private $_battlefieldId;

  /**
   * @var array Data of the battlefield
   * @access private
   */
  private $_data;

  /**
   * Constructor. Loads ruleset and call parents constructor
   *
   * @param object $DI Instance of IdependencyInjectionContainer
   * @param int $battlefieldId Id of the battlefield
   */
  public function __construct($DI, $battlefieldId){
      $this->_ruleset = new Config(__DIR__.'/../config/defaultRuleset.ini');
      $this->_battlefieldId = $battlefieldId;
      parent::__construct($DI);
  }

  /**
   * Returns battlefield's data
   *
   * @return array
   */
  public function getData(){
    if(empty($this->_data)){
      $this->_data = $this->_db->fetchFirstRequest('getBattlefieldById', array(':battlefieldId' => $this->_battlefieldId));
      $this->_data['hives'] = array();
      $hivesData = $this->_db->fetchAllRequest('getBattlefieldHiveList', array(':battlefieldId' => $this->_battlefieldId));
      foreach($hivesData as $data){
	$data['color'] = unserialize($data['color']);
	$this->_data['hives'][] = $data;
      }
    }
    return $this->_data;
  }


  /**
   * Generates a battlefield map and returns its path
   *
   * @return string path to the map, relative to the www folder
   */
  public function generateMap(){
    if(!$this->_createMapFolder()){
      return '';
    }
    $latest = $this->_getLatestGenerated();
    $currentTime = time();
    $ttl = $this->_ruleset->get('map.TTL');
    if($currentTime < $latest['time'] + $ttl){
      return 'maps/'.$this->_battlefieldId.'/'.$latest['name'];
    }
    $name = $this->_generate();
    return 'maps/'.$this->_battlefieldId.'/'.$name;
  }

  /**
   * Generate the map
   *
   * @return string The map name.
   */
  private function _generate(){
    // Get map dimensions
    $minX = $this->_ruleset->get('map.minX');
    $maxX = $this->_ruleset->get('map.maxX');
    $minY = $this->_ruleset->get('map.minY');
    $maxY = $this->_ruleset->get('map.maxY');
    
    // Create the canva
    $img = imagecreatetruecolor($maxX - $minX, $maxY - $minY);
    $white = imagecolorallocate($img, 255, 255, 255);
    imagefill($img, 0, 0, $white);

    // Allocate colors
    $hiveColors = $this->_db->fetchAllRequest('getHiveColors', array(':battlefieldId' => $this->_battlefieldId));
    foreach($hiveColors as $i => $hive){
      $color = unserialize($hive['color']);
      $soldier = imagecolorallocate($img, $color['soldier']['r'], $color['soldier']['g'], $color['soldier']['b']);
      $hq = imagecolorallocate($img, $color['hq']['r'], $color['hq']['g'], $color['hq']['b']);
      $colors[$hive['ID']] = array('soldier' => $soldier, 'hq' => $hq);
    }

    $neutralHq = imagecolorallocate($img, 0, 0, 0);
    $colors[0] = array('hq' => $neutralHq);

    // Draw headquarters
    $result = $this->_db->executeRequest('getBattlefieldHq', array(':battlefieldId' => $this->_battlefieldId,
								   ':minx' => $minX,
								   ':maxx' => $maxX,
								   ':miny' => $minY,
								   ':maxy' => $maxY));
    while($hq = $result->fetch()){
      $left = $hq['X'] - $minX;
      $top = $maxY - $hq['Y'];
      imagesetpixel($img, $left, $top, $colors[$hq['hive_id']]['hq']);
    }
    
    // Draw soldiers
    $result = $this->_db->executeRequest('getBattlefieldSoldiers', array(':battlefieldId' => $this->_battlefieldId,
									 ':minx' => $minX,
									 ':maxx' => $maxX,
									 ':miny' => $minY,
									 ':maxy' => $maxY));
    while($soldier = $result->fetch()){
      $left = $soldier['X'] - $minX;
      $top = $maxY - $soldier['Y'];
      imagesetpixel($img, $left, $top, $colors[$soldier['hive_id']]['soldier']);
    }
    
    // Save picture
    $pictureName = time().'.png';
    $picturePath = __DIR__.'/../www/maps/'.$this->_battlefieldId.'/'.$pictureName;
    imagepng($img, $picturePath);
    return $pictureName;
  }

  /**
   * Creates a map folder if needed
   *
   * @return bool True if the folder exists
   */
  private function _createMapFolder(){
    $dirPath = __DIR__.'/../www/maps/'.$this->_battlefieldId;
    if(!file_exists($dirPath)){
      return mkdir($dirPath);
    }
    return true;
  }

  /**
   * Returns the timestamp and the name of the latest generated map
   *
   * @return array
   */
  private function _getLatestGenerated(){
    $latest = array('time' => 0,
		    'name' => '');
    $dirPath = __DIR__.'/../www/maps/'.$this->_battlefieldId;
    $dir = new DirectoryIterator($dirPath);
    foreach($dir as $infos){
      if($infos->isFile() && $infos->getMTime() > $latest['time']){
	$latest = array('time' => $infos->getMTime(),
			'name' => $infos->getBasename());
      }
    }
    return $latest;
  }
}