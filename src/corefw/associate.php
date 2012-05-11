<?php

class Corefw_Associate {

	public $serviceList = array(); 
	public $thingList   = array(); 
	public $objectCache = array();

	static $assoc = NULL;

	static public function &getAssociate() {
		if (self::$assoc == NULL) {
			self::$assoc = new Corefw_Associate();
		}
		return self::$assoc;
	}

	public function whoCanHandle($service) {
		$filesep = '/';
		$objList = array();
		if (!isset($this->serviceList[$service])) {
			return array();
		}

		$svc = each($this->serviceList[$service]);
		//done with service list
		if ($svc === FALSE) {
			reset($this->serviceList[$service]);
			return FALSE;
		}
		$file = $svc[1];
		unset($svc);
		if (!isset($this->objectCache[$file])) {
			if(!include_once('local'.$filesep.$file)) {
				if(!include_once('src'.$filesep.$file))
					return FALSE;
			}
			$className = $this->formatClassName($file);
			$_x = new $className;
			$this->objectCache[$file] = $_x;
			$_x = null;
		}
		return $this->objectCache[$file];
	}

	public function iCanHandle($service, $file, $priority=2) {
		if ($priority == 1) {
			if (!is_array($this->serviceList[$service])) {
				$this->serviceList[$service] = array();
			}
			array_unshift($this->serviceList[$service], $file);
			reset($this->serviceList[$service]);
		} else {
			$this->serviceList[$service][] = $file;
		}
	}

	public function iCanOwn($service, $file) {
		//resets automatically
		$this->serviceList[$service] = array($file);
	}

	public function iAmA($thing, $file, $priority=2) {
		if ($priority == 1) {
			if (!is_array($this->thingList[$thing])) {
				$this->thingList[$thing] = array();
			}
			array_unshift($this->thingList[$thing], $file);
			reset($this->thingList[$thing]);
		} else {
			$this->thingList[$thing][] = $file;
		}
	}

	public function getMeA($thing) {
		$filesep = '/';
		$objList = array();
		if (!isset($this->thingList[$thing])) {
			return array();
		}

		$svc = each($this->thingList[$thing]);
		//done with thing list
		if ($svc === FALSE) {
			reset($this->thingList[$thing]);
			return FALSE;
		}
		$file = $svc[1];
		unset($svc);
		if (!isset($this->objectCache[$file])) {
			if(!include_once('local'.$filesep.$file)) {
				if(!include_once('src'.$filesep.$file))
					return FALSE;
			}
			$className = $this->formatClassName($file);
			$_x = new $className;
			$this->objectCache[$file] = $_x;
			$_x = null;
		}
		return $this->objectCache[$file];
	}


	public function formatClassName($filename) {
		$filesep = '/';
		$className = substr($filename, 0, strrpos($filename, '.'));
		$nameList  = explode($filesep, $className);
		$className = '';
		foreach ($nameList as $_n) {
			if ($className) $className .= '_';
			$className .= ucfirst($_n);
		}
		return $className;
	}
}

function associate_iCanHandle($service, $file) {
	$a = Corefw_Associate::getAssociate();
	$a->iCanHandle($service, $file);
}

function associate_iAmA($thing, $file) {
	$a = Corefw_Associate::getAssociate();
	$a->iAmA($thing, $file);
}

function associate_getMeA($thing) {
	$a = Corefw_Associate::getAssociate();
	return $a->getMeA($thing);
}
