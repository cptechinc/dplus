<?php namespace Dplus\Filters;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\ProcessWire;

/**
 * SortFilter
 * Class that Contains Sort and Filter Data for use for Dplus Filters
 *
 * @property string $orderBY
 */
class SortFilter extends WireData {
	const SESSION_KEY = 'sortfilter';
	private static $pw;

	public function __construct() {
		parent::__construct();
		$this->data['orderby'] = '';
		$this->data['q'] = '';
		$this->data['filters'] = [];
	}

	/**
	 * Fill Data from Array
	 * @param  array  $data
	 * @return void
	 */
	public function fillFromArray(array $data) {
		if (empty($data['orderby']) === false) {
			$this->orderby = $data['orderby'];
		}
		if (empty($data['q']) === false) {
			$this->q = $data['q'];
		}
		if (empty($data['filters']) === false) {
			$this->filters = $data['filters'];
		}
	}

	/**
	 * Save Sort Filter to Session
	 * @param  string $ns  Namespace
	 * @param  string $key Key
	 * @return void
	 */
	public function saveToSession($ns = '', $key = '') {
		$key = self::getSessionKey($key);
		if (empty($ns) === false) {
			return self::pw('session')->setFor($ns, $key, $this);
		}
		return self::pw('session')->set($key, $this);
	}

	/**
	 * Create Instance from Array
	 * @param  array       $data
	 * @return SortFilter
	 */
	public static function fromArray(array $data) {
		$filter = new self();
		$filter->fillFromArray($data);
		return $filter;
	}

	/**
	 * Return SortFilter from Session if it exists
	 * @param  string $ns   Namespace
	 * @param  string $key  Key
	 * @return SortFilter|null
	 */
	public static function getFromSession($ns = '', $key = '') {
		$key = self::getSessionKey($key);
		if (empty($ns) === false) {
			return self::pw('session')->getFor($ns, $key);
		}
		return self::pw('session')->get($key);
	}

	/**
	 * Delete Sort Filter From Session
	 * @param  string $ns   Namespace
	 * @param  string $key  Key
	 * @return void
	 */
	public static function removeFromSession($ns = '', $key = '') {
		$key = self::getSessionKey($key);
		if (empty($ns) === false) {
			return self::pw('session')->removeFor($ns, $key);
		}
		return self::pw('session')->remove($key);
	}

	/**
	 * Return Session Key
	 * @param  string $key
	 * @return string
	 */
	public static function getSessionKey($key = '') {
		if (empty($key)) {
			return self::SESSION_KEY;
		}
		return self::SESSION_KEY . "-$key";
	}

	/**
	 * Return the current ProcessWire Wire Instance
	 * @param  string            $var   Wire Object
	 * @return ProcessWire|mixed
	 */
	public static function pw($var = '') {
		if (empty(self::$pw)) {
			self::$pw = ProcessWire::getCurrentInstance();
		}
		return $var ? self::$pw->wire($var) : self::$pw;
	}
}
