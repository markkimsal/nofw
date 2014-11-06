<?php

class Nofw_Associate {

	public $serviceList  = array();
	public $thingList    = array();
	public $thingArgList = array();
	public $varList      = array();
	public $objectCache  = array();

	static $assoc = NULL;

	public function __construct() {
		//register shutdown functions execute in a different
		// directory. we need to set include path. for exception lifecycle
		//root/src/nofw/associate.php
		set_include_path(
			get_include_path().':'.dirname(dirname(dirname(__FILE__)))
		);
	}

	static public function &getAssociate() {
		if (self::$assoc == NULL) {
			self::$assoc = new Nofw_Associate();
		}
		return self::$assoc;
	}

	/**
	 * Get an object or callback reference for who can handle this service.
	 * @return Mixed  Object or callback array suitable for dropping into call_user_func()
	 */
	public function whoCanHandle($service) {
		$endService = 'post_'.$service;
		$calledService = $service;
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

		//you can tell the associate iCanHandle('service', $obj)
		// as well as passing it a file.
		if (is_object($svc[1])) {
			return array($svc[1], $calledService);
		}

		//you can also pass an callback array iCanHandle('service', array($obj, 'func'))
		if (is_array($svc[1])) {
			return $svc[1];
		}

		//assume iCanHandle() was passed a file string
		$file  = $svc[1];

		if ($file === FALSE)
			return FALSE;

		//callback function defaults to name of service
		$func = $calledService;

		//check for function name embedded in filename
		if (strpos($file, '::')!==FALSE) {
			list($file, $func) = explode('::', $file);
		}

		unset($svc);

		if (!$this->loadAndCache($file, $file)) {
			//can't find a file, just keep going with recursion
			return $this->whoCanHandle($service);
		}
		return array($this->objectCache[$file], $func);
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

	/**
	 * Define a file as a thing.
	 * Any extra arguments are saved and used as constructor arguments
	 */
	public function iAmA($thing, $file) {
		$this->thingList[$thing] = $file;

		$args = func_get_args();
		//remove 2 known params
		array_shift($args);
		array_shift($args);
		if (count($args)) {
			$this->thingArgList[$thing] = $args;
		}

	}

	/**
	 * Return a defined thing or an empty object (Nofw_Proto)
	 * @return object  defined thing or empty object (Nofw_Proto)
	 */
	public function & getMeA($thing) {
		if (!isset($this->thingList[$thing])) {
			$this->thingList[$thing] = 'StdClass';
		}
		if (is_object($this->thingList[$thing])) {
			return $this->thingList[$thing];
		}

		$filesep = '/';
		$objList = array();
		$file = $this->thingList[$thing];

		$args = func_get_args();
		array_shift($args);

		if (!count($args) && isset($this->thingArgList[$thing])) {
			$args = $this->thingArgList[$thing];
		}
		if (!count($args)) {
			$args = NULL;
			$cachekey = $file.':'.$thing;
		} else {
			$cachekey = $file.':'.$thing.':'.sha1(serialize($args));
		}

		if (!$this->loadAndCache($file, $cachekey, $args))
			$this->objectCache[$cachekey] = new Nofw_Proto($thing);

		return $this->objectCache[$cachekey];
	}

	/**
	 * Return a clone (deep or shallow copy) of a defined thing or an empty object (Nofw_Proto)
	 * @return object  clone of a defined thing or empty object (Nofw_Proto)
	 */
	public function getMeANew($thing) {
		if (!isset($this->thingList[$thing])) {
			$this->thingList[$thing] = 'StdClass';
		}
		if (is_object($this->thingList[$thing])) {
			return clone $this->thingList[$thing];
		}

		$filesep = '/';
		$objList = array();
		$file = $this->thingList[$thing];

		$args = func_get_args();
		array_shift($args);
		if (!count($args) && isset($this->thingArgList[$thing])) {
			$args = $this->thingArgList[$thing];
		}

		if (!count($args)) {
			$args = NULL;
			$cachekey = $file;
		} else {
			$cachekey = $file.':'.sha1(serialize($args));
		}

		if (!$this->loadAndCache($file, $cachekey, $args))
			return new Nofw_Proto($thing);

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

		if(!file_exists('src'.$filesep.$file)) {
			if(!@include_once('local'.$filesep.$file)) {
				return FALSE;
			}
		} else {
			if(!include_once('src'.$filesep.$file)) {
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
		$className = str_replace('-', '', $className);
		return $className;
	}

	/**
	 * Return true if there is any handler defined for a service
	 */
	public function hasHandlers($service) {
		$post = 'post_'.$service;
		return (
			(isset($this->serviceList[$service]) &&
			is_array($this->serviceList[$service]) &&
			count($this->serviceList[$service]) > 0)
			||
			(isset($this->serviceList[$post]) &&
			is_array($this->serviceList[$post]) &&
			count($this->serviceList[$post]) > 0)
			);
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
	$args = func_get_args();
	if (count($args) <= 2) {
		return $a->iAmA($thing, $file);
	} else {
		return call_user_func_array(array($a, 'iAmA'), $args);
	}
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

function associate_hasHandlers($service) {
	$a = Nofw_Associate::getAssociate();
	return $a->hasHandlers($service);
}


function _iCanHandle($service, $file, $priority=2) {
	$a = Nofw_Associate::getAssociate();
	$a->iCanHandle($service, $file, $priority);
}

function _iCanOwn($service, $file) {
	$a = Nofw_Associate::getAssociate();
	$a->iCanOwn($service, $file);
}

function _iAmA($thing, $file) {
	$a = Nofw_Associate::getAssociate();
	$args = func_get_args();
	if (count($args) <= 2) {
		return $a->iAmA($thing, $file);
	} else {
		return call_user_func_array(array($a, 'iAmA'), $args);
	}
}

function _getMeA($thing) {
	$a = Nofw_Associate::getAssociate();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->getMeA($thing);
	} else {
		return call_user_func_array(array($a, 'getMeA'), $args);
	}
	return $a->getMeA($thing);
}

function _getMeANew($thing) {
	$a = Nofw_Associate::getAssociate();
	$args = func_get_args();
	if (count($args) <= 1) {
		return $a->getMeANew($thing);
	} else {
		return call_user_func_array(array($a, 'getMeANew'), $args);
	}
}

function _set($key, $val) {
	$a = Nofw_Associate::getAssociate();
	return $a->set($key, $val);
}

function _get($key, $def=NULL) {
	$a = Nofw_Associate::getAssociate();
	return $a->get($key, $def);
}

function _hasHandlers($service) {
	$a = Nofw_Associate::getAssociate();
	return $a->hasHandlers($service);
}

