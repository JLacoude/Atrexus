<?php
/**
 * ConfigTest
 *
 * @package Atrexus
 * @subpackage Tests
 * @author Jeremy Lacoude
 */
require_once __DIR__.'/../classes/Config.php';

class ConfigTest extends PHPUnit_Framework_TestCase{
  public function testGet(){
    $config = new Config(__DIR__.'/test.ini');

    $empty = $config->get('emptyKey');
    $this->assertEmpty($empty);
    $this->assertTrue(isset($empty));

    $containSomething = $config->get('notEmptyKey');
    $this->assertEquals($containSomething, 'some content');

    $containTheSameThing = $config->get('notEmptyKey', 'another content');
    $this->assertEquals($containSomething, 'some content');

    $stillEmpty = $config->get('emptyKey', 'another content');
    $this->assertEmpty($stillEmpty);

    $notSet = $config->get('notSetKey');
    $this->assertNull($notSet);

    $set = $config->get('notSetKey', 'it is set now');
    $this->assertEquals($set, 'it is set now');

    $stillSet = $config->get('notSetKey');
    $this->assertEquals($stillSet, 'it is set now');    
  }

  public function testIniFileNotFound(){
    $this->assertFileNotExists(__DIR__.'/notfound.ini');
    $this->setExpectedException('Exception');
    $config = new Config(__DIR__.'/notfound.ini');
  }

  public function testIniFileMalformed(){
    $this->setExpectedException('Exception');
    $config = new Config(__DIR__.'/malformed.ini');
  }

  public function testGetAll(){
    $config = new Config(__DIR__.'/test.ini');

    $all = $config->getAll();
    $this->assertEquals($all, array('emptyKey' => '',
				    'notEmptyKey' => 'some content'));
  }
}
