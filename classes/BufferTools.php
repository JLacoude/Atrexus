<?php
/**
 * BufferTools
 * Defines methods used to manage the output buffer
 *
 * @package Atrexus
 * @author Jeremy Lacoude
 */
class BufferTools{
  /**
   * Starts an output buffer
   * Can be limited to a level and be gziped
   *
   * @param int $maxLevel Level limit 0 or less for no limit, defaults to 0
   * @param bool $gzipped Specify if output must be gzipped, default to true
   *
   * @return bool false if the buffer has not been created
   */
  public static function startOutputBuffer($maxLevel = 0, $gzipped = true){
    if($maxLevel > 0 && ob_get_level() >= $maxLevel){
      return false;
    }
    
    if(!ini_get('zlib.output_compression') && $gzipped){
      return ob_start('ob_gzhandler');
    }
    
    return ob_start();
  }

  /**
   * Cleans all buffer until some level
   *
   * @param int $level Lower level buffer to wipe. Default to 0 (cleans all output buffers)
   */
  public static function cleanOutputBuffers($level = 0){
    while(ob_get_level() > $level + (ini_get('zlib.output_compression')?1:0)){
      ob_end_clean();
    }
  }
}