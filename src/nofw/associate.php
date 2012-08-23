<?php

class Nofw_Associate {

	public $serviceList = array(); 
	public $thingList   = array(); 
	public $varList     = array(); 
	public $objectCache = array();

	static $assoc = NULL;

	static public function &getAssociate() {
		if (self::$assoc == NULL) {
			self::$assoc = new Nofw_Associate();
		}
		return self::$assoc;
	}

	public function whoCanHandle($service) {
		$endService = 'post_'.$service;
		//maybe we have only a post service (priority = 3)
		if (!isset($this->serviceList[$service])) {
			$service = $endService;
		}

		//maybe we have no services
		if (!isset($this->serviceList[$service])) {
			return FALSE;
		}

		$filesep = '/';
		$objList = array();

		$svc = each($this->serviceList[$service]);
		//done with service list
		if ($svc === FALSE && !isset($this->serviceList[$endService])) {
			reset($this->serviceList[$service]);
			return FALSE;
		}
		//not done with post_service list
		if ($svc == FALSE) {
			$svc = each($this->serviceList[$endService]);
			if ($svc === FALSE) {
				reset($this->serviceList[$service]);
				reset($this->serviceList[$endService]);
				return FALSE;
			}
		}

		$file = $svc[1];
		unset($svc);
		//you can tell the associate iCanHandle('service', $object)
		// as well as passing it a file.
		if (is_object($file)) {
			return $file;
		}

		if (!isset($this->objectCache[$file])) {
			if(!file_exists('local'.$filesep.$file)) {
				if(!@include_once('src'.$filesep.$file))
					//can't find a file, just keep going with recursion
					return $this->whoCanHandle($service);
			} else {
				if(!include_once('local'.$filesep.$file)) {
					//found the file, but it has an error, keep going with recursion
					return $this->whoCanHandle($service);
				}
			}
			$className = $this->formatClassName($file);
			$_x = new $className;
			$this->objectCache[$file] = $_x;
			$_x = null;
		}
		return $this->objectCache[$file];
	}

	public function iCanHandle($service, $file, $priority=2) {
		if ($priority == 3) {
			$service = 'post_'.$service;
		}
		if ($priority == 1) {
			if (!isset($this->serviceList[$service])) {
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
		$endService = 'post_'.$service;
		if (isset($this->serviceList[$endService])) {
			$this->serviceList[$endService] = array();
		}
	}

	public function iAmA($thing, $file) {
		$this->thingList[$thing] = $file;
	}

	/**
	 * Return a defined thing or an empty object (StdClass)
	 * @return object  defined thing or empty object (StdClass)
	 */
	public function & getMeA($thing) {
		if (!isset($this->thingList[$thing])) {
			$this->thingList[$thing] = 'StdClass';
			$this->objectCache[$thing] = array(new StdClass);
		}
		$filesep = '/';
		$objList = array();
		$file = $this->thingList[$thing];

		$args = func_get_args();
		array_shift($args);
		if (!count($args)) {
			$args = NULL;
			$cachekey = $file.':'.$thing;
		} else {
			$cachekey = $file.':'.$thing.':'.sha1(serialize($args));
		}

		if (!$this->loadAndCache($file, $cachekey, $args))
			$this->objectCache[$cachekey] = new StdClass();

		return $this->objectCache[$cachekey];
	}

	/**
	 * Return a clone (deep or shallow copy) of a defined thing or an empty object (StdClass)
	 * @return object  clone of a defined thing or empty object (StdClass)
	 */
	public function getMeANew($thing) {
		if (!isset($this->thingList[$thing])) {
			$this->thingList[$thing] = 'StdClass';
			$this->objectCache[$thing] = array(new StdClass);
		}
		$filesep = '/';
		$objList = array();
		$file = $this->thingList[$thing];

		$args = func_get_args();
		array_shift($args);
		if (!count($args)) {
			$args = NULL;
			$cachekey = $file;
		} else {
			$cachekey = $file.':'.sha1(serialize($args));
		}

		if (!$this->loadAndCache($file, $cachekey, $args))
			return StdClass();

		return clone $this->objectCache[$cachekey];
	}

	/**
	 * load a file from local/$file or src/$file.
	 * Save object to $this->objectCache[$cachekey]
	 *
	 * @return  Boolean True if file was loaded and saved
	 */
	public function loadAndCache($file, $cachekey, $args=NULL) {
		if (isset($this->objectCache[$cachekey])) {
			return TRUE;
		}
		//if something is undefined, its 'file' in the thingList is set to StdClass
		if ($file === 'StdClass') return FALSE;

		$filesep = '/';

		if(!file_exists('local'.$filesep.$file)) {
			if(!@include_once('src'.$filesep.$file)) {
				return FALSE;
			}
		} else {
			if(!@include_once('local'.$filesep.$file)) {
				return FALSE;
			}
		}
		$className = $this->formatClassName($file);
		if (is_array($args) && class_exists('ReflectionClass', false)) {
			$refl = new ReflectionClass($className);
			try {
				$_x = $refl->newInstanceArgs($args);
			} catch (ReflectionException $e) {
				$_x = $refl->newInstance();
			}
		} else {
			$_x = new $className;
		}

		$this->objectCache[$cachekey] = $_x;
		$_x = null;
		return TRUE;
	}

	public function set($key, $val) {
		$this->varList[$key] = $val;
	}

	public function get($key, $default=NULL) {
		if (!isset($this->varList[$key]))
			return $default;

		return $this->varList[$key];
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

function associate_iCanHandle($service, $file, $priority=2) {
	$a = Nofw_Associate::getAssociate();
	$a->iCanHandle($service, $file, $priority);
}

function associate_iCanOwn($service, $file) {
	$a = Nofw_Associate::getAssociate();
	$a->iCanOwn($service, $file);
}

function associate_iAmA($thing, $file) {
	$a = Nofw_Associate::getAssociate();
	$a->iAmA($thing, $file);
}

function associate_getMeA($thing) {
	$a = Nofw_Associate::getAssociate();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->getMeA($thing);
	} else {
		return call_user_func_array(array($a, 'getMeA'), $args);
	}
	return $a->getMeA($thing);
}

function associate_getMeANew($thing) {
	$a = Nofw_Associate::getAssociate();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->getMeANew($thing);
	} else {
		return call_user_func_array(array($a, 'getMeANew'), $args);
	}
}

function associate_set($key, $val) {
	$a = Nofw_Associate::getAssociate();
	return $a->set($key, $val);
}

function associate_get($key, $def=NULL) {
	$a = Nofw_Associate::getAssociate();
	return $a->get($key, $def);
}
