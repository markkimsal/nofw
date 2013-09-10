<?php

include_once('src/nofw/associate.php');
include_once('src/nofw/proto.php');

class Nofw_Tests_Associate extends UnitTestCase { 

	public function setUp() {
	}

	public function test_SkipBadHandlers() {
		_iCanHandle('testphase1', 'non/existant.php');
		_iCanHandle('testphase1', 'non/existant2.php');
		$a = Nofw_Associate::getAssociate();
		while ($svc = $a->whoCanHandle('testphase1')) {
			$this->assertFail(true);
		}
	}

	/**
	 * Ensure that iCanHandle produces the correct number of handler objects
	 */
	public function test_FindHandlers() {
		_iCanHandle('testphase2', 'nofw/tests/emptyhandler.txt');
		_iCanHandle('testphase2', 'nofw/tests/emptyhandler.txt');
		$a = Nofw_Associate::getAssociate();
		$count = 0;
		$prevsvc = array();
		while ($svc = $a->whoCanHandle('testphase2')) {
			$prevsvc = $svc;
			++$count;
		}
		$this->assertTrue( is_object($prevsvc[0]) );
		$this->assertEqual( 2, $count );
	}

	/**
	 * Ensure you can pass an existing object reference as
	 * a lifecycle handler
	 */
	public function test_UseRealObjectsAsHandlers() {
		_iAmA('emptyhandler', 'nofw/tests/emptyhandler.txt');
		$obj = _getMeA('emptyhandler');
		_iCanHandle('testphase3', $obj);
		$a = Nofw_Associate::getAssociate();
		$prevsvc = array();
		while ($svc = $a->whoCanHandle('testphase3')) {
			$prevsvc = $svc;
		}
		$this->assertTrue( is_object($prevsvc[0]) );
	}

	/**
	 * Ensure you can pass a user call back array as a lifecycle 
	 * handler
	 */
	public function test_UseArrayCallbackAsHandlers() {
		_iAmA('emptyhandler', 'nofw/tests/emptyhandler.txt');
		$obj = _getMeA('emptyhandler');
		_iCanHandle('testphase4', array(&$obj, 'dummyfunc'));
		$a = Nofw_Associate::getAssociate();
		$svc = $a->whoCanHandle('testphase4');
		$this->assertTrue( is_array($svc) );
		$this->assertTrue( is_object($svc[0]) );
		$this->assertEqual( 'dummyfunc', ($svc[1]) );
	}
}
