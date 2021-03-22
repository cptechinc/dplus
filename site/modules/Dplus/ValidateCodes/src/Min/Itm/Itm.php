<?php namespace Dplus\CodeValidators\Min;

use ProcessWire\WireData;

use Propel\Runtime\ActiveQuery\Criteria;

use ItemMasterItemQuery, ItemMasterItem;

use Dplus\CodeValidators\Min;
use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Mar as MarValidator;

/**
 * Itm
 *
 * Class for Validating ITM codes
 */
class Itm extends Min {
/* =============================================================
	Itm Functions
============================================================= */
	/**
	 * Return if Preference Value is valid
	 * @param  string $value  Preference Value
	 * @return bool
	 */
	public function preference($value) {
		return in_array($value, ItemMasterItem::OPTIONS_PREFERENCE);
	}

	/**
	 * Return if Producer Value is valid
	 * @param  string $value  Producer Value
	 * @return bool
	 */
	public function producer($value) {
		return array_key_exists($value, ItemMasterItem::OPTIONS_PRODUCER);
	}

	/**
	 * Return if Preference Value is valid
	 * @param  string $value  Preference Value
	 * @return bool
	 */
	public function documentation($value) {
		return in_array($value, ItemMasterItem::OPTIONS_DOCUMENTATION);
	}

/* =============================================================
	Itm Hazmat Functions
============================================================= */
	/**
	 * Return if Group is valid Hazmat Pack Group
	 * @param  string $group
	 * @return bool
	 */
	public function hazmat_packgroup($group) {
		return in_array($group, InvHazmatItem::OPTIONS_PACKGROUP);
	}

	/**
	 * Return if $class is valid Hazmat Class
	 * @param  string $class
	 * @return bool
	 */
	public function hazmat_class($group) {
		return true;
	}

	/**
	 * Return if $nbr is valid UN Nbr
	 * @param  string $nbr UN Nbr
	 * @return bool
	 */
	public function hazmat_unnbr($nbr) {
		return true;
	}

	/**
	 * Return if $label is valid Label Value
	 * @param  string $label Label
	 * @return bool
	 */
	public function hazmat_label($label) {
		return true;
	}

	/**
	 * Return if Hazmat Allow Air value is valid
	 * @param  string $val Allow Air
	 * @return bool
	 */
	public function hazmat_allowair($val) {
		return in_array(strtoupper($val), ['Y', 'N']);
	}

	/**
	 * Return if Hazmat Dot 1 is valid
	 * @param  string $val DOT Ship Name
	 * @return bool
	 */
	public function hazmat_dot1($val) {
		return $val != '';
	}

	/**
	 * Return if Hazmat Dot 2 is valid
	 * @param  string $val DOT Ship Name
	 * @return bool
	 */
	public function hazmat_dot2($val) {
		return true;
	}

/* =============================================================
	MAR Functions
============================================================= */
	/**
	 * Return if Cust ID is Valid
	 * @param  string $custID Customer ID
	 * @return bool
	 */
	public function custid($custID) {
		$validate = new MarValidator();
		return $validate->custid($custID);
	}

/* =============================================================
	MAP Functions
============================================================= */
	/**
	 * Validate AP Buyer Code
	 * @param  string $code AP Buyer Code
	 * @return bool
	 */
	public function buyercode($code) {
		$validate = new MapValidator();
		return $validate->buyercode($code);
	}

/* =============================================================
	MSO Functions
============================================================= */
	/**
	 * Return if Motor Freight Code is valid
	 * @param  string $code  Motor Freight Code
	 * @return bool
	 */
	public function freightcode($code) {
		$validate = new MsoValidator();
		return $validate->freightcode($code);
	}
}
