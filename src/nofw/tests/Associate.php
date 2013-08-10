<?php

class Nofw_Test_Associate extends UnitTestCase { 


	public function setUp() {
		include_once('src/nofw/associate.php');
	}
	public function test_SkipBadHandlers() {
		_iCanHandle('testphase1', 'non/existant.php');
		_iCanHandle('testphase1', 'non/existant2.php');
		$a = Nofw_Associate::getAssociate();
		while ($svc = $a->whoCanHandle('testphase1')) {
			$this->assertFail(true);
		}
	}

	public function test_FindComponentHandlers() {
		_iCanHandle('testphase2', 'nofw-simpletest/emptyhandler.php');
		$a = Nofw_Associate::getAssociate();
		while ($svc = $a->whoCanHandle('testphase2')) {
			$this->assertTrue( is_object($svc[0]) );
		}
	}

	public function test_UseRealObjectsAsHandlers() {
		_iAmA('emptyhandler', 'nofw-simpletest/emptyhandler.php');
		$obj = _getMeA('emptyhandler');
		_iCanHandle('testphase3', $obj);
		$a = Nofw_Associate::getAssociate();
		while ($svc = $a->whoCanHandle('testphase3')) {
			$this->assertTrue( is_object($svc[0]) );
		}
	}

	public function test_UseArrayCallbackAsHandlers() {
		_iAmA('emptyhandler', 'nofw-simpletest/emptyhandler.php');
		$obj = _getMeA('emptyhandler');
		_iCanHandle('testphase4', array(&$obj, 'dummyfunc'));
		$a = Nofw_Associate::getAssociate();
		$svc = $a->whoCanHandle('testphase4');
		$this->assertTrue( is_array($svc) );
		$this->assertTrue( is_object($svc[0]) );
		$this->assertEqual( 'dummyfunc', ($svc[1]) );
	}
}
