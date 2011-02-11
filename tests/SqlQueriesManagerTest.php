<?php
/**
 * SqlQueriesManagerTest
 *
 * @package Atrexus
 * @subpackage Tests
 * @author Jeremy Lacoude
 */
require __DIR__.'/../classes/SqlQueriesManager.php';

class SqlQueriesManagerTest extends PHPUnit_Framework_TestCase{
  protected function setUp(){
    if(!file_exists(__DIR__.'/../sql/phpunit_requests.ini')){
      file_put_contents(__DIR__.'/../sql/phpunit_requests.ini',
			'testQuery="SELECT NULL"');
    }
    if(file_exists(__DIR__.'/../sql/phpunit_fail_requests.ini')){
      unlink(__DIR__.'/../sql/phpunit_fail_requests.ini');
    }
  }
  
  public function testGet(){
    $sqlManager = new SqlQueriesManager('phpunit');
    
    $query = $sqlManager->get('testQuery');
    $this->assertEquals($query, 'SELECT NULL');

    $this->setExpectedException('Exception');
    $query = $sqlManager->get('noQuery');
  }

  public function testIniFileNotFound(){
    $this->setExpectedException('Exception');
    $sqlManager = new SqlQueriesManager('phpunit_fail');
  }
}
