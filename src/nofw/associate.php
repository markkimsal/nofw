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
		if (!isset($this->serviceList[$service])) {
			return FALSE;
		}

		$filesep = '/';
		$objList = array();

		$svc = each($this->serviceList[$service]);
		//done with service list
		if ($svc === FALSE) {
			reset($this->serviceList[$service]);
			return FALSE;
		}
		$file = $svc[1];
		unset($svc);
		if (!isset($this->objectCache[$file])) {
			if(!file_exists('local'.$filesep.$file)) {
				if(!@include_once('src'.$filesep.$file))
					return FALSE;
			} else {
				if(!include_once('local'.$filesep.$file)) {
					return FALSE;
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

	public function iAmA($thing, $file) {
		$this->thingList[$thing] = $file;
	}

	/**
	 * Return a defined thing or an empty object (StdClass)
	 * @return object  defined thing or empty object (StdClass)
	 */
	public function getMeA($thing) {
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


		if (!isset($this->objectCache[$cachekey])) {

			if(!file_exists('local'.$filesep.$file)) {
				if(!@include_once('src'.$filesep.$file))
					return FALSE;
			} else {
				if(!include_once('local'.$filesep.$file)) {
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
		}
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

		if (!isset($this->objectCache[$cachekey])) {

			if(!file_exists('local'.$filesep.$file)) {
				if(!@include_once('src'.$filesep.$file))
					return FALSE;
			} else {
				if(!include_once('local'.$filesep.$file)) {
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
		}
		return clone $this->objectCache[$cachekey];
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

function associate_iCanHandle($service, $file) {
	$a = Nofw_Associate::getAssociate();
	$a->iCanHandle($service, $file);
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
