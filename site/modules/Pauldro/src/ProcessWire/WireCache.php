<?php namespace ProcessWire\Cache\WireCache;
// ProcessWire
use ProcessWire\Wire;
use ProcessWire\WireCache as PwWireCache;

/**
 * WireCache
 * 
 * Extends WireCache 
 */
class WireCache extends PwWireCache {
	private static $instance;

	public static function instance() {
		if (empty(self::$instance) === false) {
			return self::$instance;
		}
		self::$instance = new self();
		return self::$instance;
	}
	
	public function exists($name, $expire = null, $func = null) {
		return boolval($this->count($name, $expire, $func));
	}

	public function existsFor($ns, $name, $expire = null, $func = null) {
		if(is_object($ns)) $ns = $this->wireClassName($ns, false); 
		return boolval($this->count($ns . "__$name"));
	}

	public function count($name, $expire = null, $func = null) {
	
		$_expire = $expire;

		if(!is_null($expire)) {
			if(!is_int($expire) && !is_string($expire) && !$expire instanceof Wire && is_callable($expire)) {
				$_func = $func;
				$func = $expire; 
				$expire = is_null($_func) ? null : $this->getExpires($_func);
				unset($_func);
			} else {
				$expire = $this->getExpires($expire);
			}
		}

		$names = array($name); 	
		
		$where = array();
		$binds = array();
		$wildcards = array();
		$n = 0;
		
		foreach($names as $name) {
			$n++;
			if(strpos($name, '*') !== false || strpos($name, '%') !== false) {
				// retrieve all caches matching wildcard
				$wildcards[$name] = $name; 
				$name = str_replace('*', '%', $name); 
				$where[$n] = "name LIKE :name$n";
			} else {
				$where[$n] = "name=:name$n";
			}
			$binds[":name$n"] = $name; 
		}
		
		$sql = "SELECT COUNT(*) FROM caches WHERE (" . implode(' OR ', $where) . ") ";
		
		if(is_null($expire)) { // || $func) {
			$sql .= "AND (expires>=:now OR expires<=:never) ";
			$binds[':now'] = date(self::dateFormat, time());
			$binds[':never'] = self::expireNever;
		} else if(is_array($expire)) {
			// expire is specified by a page selector, so we just let it through
			// since anything present is assumed to be valid	
		} else {
			$sql .= "AND expires<=:expire ";
			$binds[':expire'] = $expire;
			// $sql .= "AND (expires>=:expire OR expires<=:never) ";
			//$binds[':never'] = self::expireNever;
		}
	
		$query = $this->wire('database')->prepare($sql, "cache.get(" . 
			implode('|', $names) . ", " . ($expire ? print_r($expire, true) : "null") . ")"); 
		
		foreach($binds as $key => $value) $query->bindValue($key, $value);
		
		$value = ''; // return value for non-multi mode
		
		if($_expire !== self::expireNow) try {
			$query->execute();
			return $query->fetchColumn();
			$query->closeCursor();
				
		} catch(\Exception $e) {
			$this->trackException($e, false);
			$value = null;
		}
		
		return $value; 
	}

	public function countFor($ns, $name, $expire = null, $func = null) {
		if(is_object($ns)) $ns = $this->wireClassName($ns, false); 
		return $this->count($ns . "__$name", $expire, $func); 
	}

	/**
	 * Normalize a class name with or without namespace, or get namespace of class
	 *
	 * Default behavior is to return class name without namespace.
	 *
	 * #pw-group-class-helpers
	 *
	 * @param string|object $className Class name or object instance
	 * @param bool|int|string $withNamespace Should return value include namespace? (default=false)
	 *  - `false` (bool): Return only class name without namespace (default).
	 *  - `true` (bool): Yes include namespace in returned value.
	 *  - `1` (int): Return only namespace (i.e. “ProcessWire”, with no backslashes unless $verbose argument is true)
	 * @param bool $verbose When namespace argument is true or 1, use verbose return value (added 3.0.143). This does the following:
	 *  - If returning class name with namespace, this makes it include a leading backslash, i.e. `\ProcessWire\Wire`
	 *  - If returning namespace only, adds leading backslash, plus trailing backslash if namespace is not root, i.e. `\ProcessWire\`
	 * @return string|null Returns string or NULL if namespace-only requested and unable to determine
	 *
	 */
	protected function wireClassName($className, $withNamespace = false, $verbose = false) {

		$bs = "\\"; // backslash

		if(is_object($className)) {
			$object = $className;
			$className = get_class($className);
		} else {
			$object = null;
		}

		$pos = strrpos($className, $bs);

		if($withNamespace === true) {
			if($object) { 
				// result of get_class() is already what we want
			} else if($pos === false && __NAMESPACE__) {
				// return class with namespace, substituting ProcessWire namespace if none present
				$className = __NAMESPACE__ . $bs . $className;
			}
			if($verbose) {
				// add leading backslash
				$className = $bs . ltrim($className, $bs);
			}
			
		} else if($withNamespace === 1) {
			// return namespace only
			if($pos !== false) {
				// there is a namespace, extract it
				$className = substr($className, 0, $pos);
			} else if($object) {
				// namespace is root
				$className = $verbose ? $bs : '';
			} else {
				// there is no namespace in given className, attempt to detect in ProcessWire or root namespace
				if(class_exists(__NAMESPACE__ . $bs . $className)) {
					// class in ProcessWire namespace
					$className = __NAMESPACE__;
				} else if(class_exists($bs . $className)) {
					// class in root namespace
					$className = '';
				} else {
					// unable to determine
					$className = null;
				}
			}
			if($verbose && $className !== null) {
				$className = $bs . trim($className, $bs); // leading
				if(strlen($className) > 1) $className .= $bs; // trailing
			}

		} else {
			// return className without namespace (default behavior)
			if($pos !== false) $className = substr($className, $pos+1);
		}
		return $className;
	}
}