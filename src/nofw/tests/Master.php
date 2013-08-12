<?php

include_once('src/nofw/master.php');
include_once('src/nofw/associate.php');
include_once('src/nofw/proto.php');

class Nofw_Tests_Master extends UnitTestCase { 

	public function setUp() {
	}

	public function test_EventHandling() {
		_iCanHandle('Fire', array($this, 'evtHandler'));
		_iCanHandle('Fire_post', array($this, 'evtPostHandler'));
		$x = 1;
		$y = 'a';
		$args = array($x, $y);
		$result = Nofw_Master::event('Fire', $this, $args);
		$this->assertTrue($result);
		$this->assertEqual( $args[0], 2);
		$this->assertEqual( $args[1], 'z');
	}

	public function evtHandler($evt, &$args) {
		$args[0] = 2;
		return TRUE;
	}

	public function evtPostHandler($evt, &$args) {
		$args[1] = 'z';
		return TRUE;
	}
}
