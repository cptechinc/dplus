<?php namespace Dplus\Lookup\Item;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Input Data
 */
class Input extends WireData {
	const FIELDS = [
		'itemid',
		'q',
		'custid', 'custitemid',
		'vendorid', 'vendoritemid',
		'mnfrid',   'mnfritemid',
		'upc',
		'ordn', 'qnbr', 'ponbr'
	];

	public function __construct() {
		$this->initFields();
	}

	/**
	 * Initializes Field Data
	 * @return void
	 */
	public function initFields() {
		foreach (self::FIELDS as $field) {
			$this->$field = '';
		}
	}

	/**
	 * Return Value for q, use other fields if empty
	 * @return string
	 */
	public function q() {
		if ($this->q) {
			return $q;
		}
		if ($this->itemid) {
			return $this->itemid;
		}
		return '';
	}

	/**
	 * Return Value for itemid, use other fields if empty
	 * @return string
	 */
	public function itemid() {
		if ($this->itemid) {
			return $this->itemid;
		}
		if ($this->q) {
			return $q;
		}
		return '';
	}

	/**
	 * Return Value for upc, use other fields if empty
	 * @return string
	 */
	public function upc() {
		if ($this->upc) {
			return $this->upc;
		}
		if ($this->q) {
			return $q;
		}
		if ($this->itemid) {
			return $this->itemid;
		}
		return '';
	}

	/**
	 * Return Value for itemid, use other fields if empty
	 * @return string
	 */
	public function vendoritemid() {
		if ($this->vendoritemid) {
			return $this->vendoritemid;
		}
		return $this->itemid();
	}

	/**
	 * Return Value for itemid, use other fields if empty
	 * @return string
	 */
	public function custitemid() {
		if ($this->custitemid) {
			return $this->custitemid;
		}
		return $this->itemid();
	}

	/**
	 * Return Value for itemid, use other fields if empty
	 * @return string
	 */
	public function mnfritemid() {
		if ($this->mnfritemid) {
			return $this->mnfritemid;
		}
		return $this->itemid();
	}

	/**
	 * Set Properties from Wire Input if field exists
	 * @param WireInput $input
	 */
	public function setFieldsWireInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$sanitizer = $this->wire('sanitizer');

		foreach ($values->getArray() as $key => $value) {
			$property = strtolower($key);

			if (in_array($property, self::FIELDS)) {
				$this->$property = $sanitizer->text($value);
			}
		}
	}

	/**
	 * Return Field Data
	 * @return array
	 */
	public function getData() {
		return $this->data;

	}

	/**
	 * Return Field Data (NON EMPTY)
	 * @return array
	 */
	public function getDataNotEmpty() {
		return array_filter($this->data);
	}

	/**
	 * Return if Field Value is Not empty
	 * @param  string $key Field
	 * @return bool
	 */
	public function doesFieldHaveValue($key) {
		if ($this->has($key) === false) {
			return false;
		}
		return $this->$key !== '';
	}

	/**
	 * Create Instance from Wire Input
	 * @param  WireInput $input  Wire Input
	 * @return Input
	 */
	public static function fromWireInput(WireInput $input) {
		$c = new Input();
		$c->setFieldsWireInput($input);
		return $c;
	}
}
