<?php namespace Dplus\Lookup\Item\Sources;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Lookup
use Dplus\Lookup\Item\Input;

/**
 * Factory
 * Factory class that Instantiates Sources
 */
class Factory extends WireData {
	const SOURCES = [
		'itm'              => 'Itm',
		'cxm'              => 'Cxm',
		'cxm-shortitem'    => 'CxmShortItem',
		'mxrfe'            => 'Mxrfe',
		'mxrfe-shortitem'  => 'MxrfeShortItem',
		'vxm'              => 'Vxm',
		'upcx'             => 'Upcx',
	];

	/** @var Input */
	protected $inputdata;

	/**
	 * Set Input Data
	 * @param Input
	 */
	public function setInputData(Input $data) {
		$this->inputdata = $data;
	}

	/**
	 * Return Input Data
	 * @return Input
	 */
	public function getInputData() {
		return $this->inputdata;
	}

	/**
	 * Return if Source
	 * @param  string $code Source code or Class Name
	 * @return bool
	 */
	public function sourceExists($code) {
		return array_key_exists($code, self::SOURCES) || in_array($code, self::SOURCES);
	}

	/**
	 * Return Source Class Name
	 * @param  string $code Source code or Class Name
	 * @return string
	 */
	public function sourceClassName($code) {
		if ($this->sourceExists($code) === false) {
			return '';
		}

		$ns = __NAMESPACE__;
		$class = $code;
		if (array_key_exists($code, self::SOURCES)) {
			$class = self::SOURCES[$code];
		}
		return "\\$ns\\$class";
	}

	/**
	 * Return Source Class
	 * @param  string $code Source code or Class Name
	 * @return Source
	 */
	public function getSource($code) {
		if ($this->sourceExists($code) === false) {
			return false;
		}
		$class = $this->sourceClassName($code);
		$src = new $class();
		if (empty($this->inputdata) === false) {
			$src->setInputData($this->inputdata);
		}
		return $src;
	}

	public function getSources(array $codes) {
		$sources = [];

		foreach ($codes as $code) {
			if ($this->sourceExists($code)) {
				$key = $this->getSourceKey($code);
				$sources[$key] = $this->getSource($code);
			}
		}
		return $sources;
	}

	public function getSourceKey($code) {
		if ($this->sourceExists($code) === false) {
			return '';
		}
		if (array_key_exists($code, self::SOURCES)) {
			return $code;
		}
		$classes = array_flip(self::SOURCES);
		return $classes[$code];
	}
}
