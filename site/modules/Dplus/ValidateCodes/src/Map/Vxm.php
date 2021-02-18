<?php namespace Dplus\CodeValidators\Map;

use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Min as MinValidator;

use ItemXrefVendorQuery, ItemXrefVendor;

/**
 * VXM
 *
 * Class for validating VXM properties
 */
class Vxm extends MapValidator {
/* =============================================================
	IN Functions
============================================================= */
	/**
	 * Return if Item ID is Valid
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function itemid($itemID) {
		$validate = new MinValidator();
		return $validate->itemid($itemID);
	}

	/**
	 * Return if Unit of Measure Purchase Code is valid
	 * @param  string $code Unit of Measure Sale Code
	 * @return bool
	 */
	public function unitofm_purchase($code) {
		$validate = new MinValidator();
		return $validate->unitofm_purchase($code);
	}

/* =============================================================
	VXM Functions
============================================================= */
	/**
	 * Return if PO order code Exists
	 * @param  string $option PO Order Code Option
	 * @return bool
	 */
	public function ordercode($option) {
		return array_key_exists($option, ItemXrefVendor::OPTIONS_POORDERCODE);
	}

	/**
	 * Return if approval code Exists
	 * @param  string $option Approval Code
	 * @return bool
	 */
	public function approvalcode($option) {
		return array_key_exists($option, ItemXrefVendor::OPTIONS_APPROVALCODE);
	}

	/**
	 * Validate if Vendor ID has a X-ref for Item ID
	 * @param  string $vendorID Vendor ID
	 * @param  string $itemID   Item ID
	 * @return bool
	 */
	public function vendor_has_xref_itemid($vendorID, $itemID) {
		if ($this->vendorid($vendorID) === false || $this->itemid($itemID) === false) {
			return false;
		}
		$q = ItemXrefVendorQuery::create();
		$q->filterByItemid($itemID)->filterByVendorid($vendorID);
		return boolval($q->count());
	}

	/**
	 * Validate if Vendor Item ID matches Item ID
	 * @param  string $vendoritemID Vendor Item ID
	 * @param  string $itemID       Our Item ID
	 * @return bool
	 */
	public function vendoritemid_matches_itemid($vendoritemID, $itemID) {
		if ($this->itemid($itemID) === false) {
			return false;
		}
		$q = ItemXrefVendorQuery::create()->filterByItemid($itemID)->filterByVendoritemid($vendoritemID);
		return boolval($q->count());
	}

	/**
	 * Validate if VXM X-ref exists
	 * @param  string $vendorID     Vendor ID
	 * @param  string $vendoritemID Vendor Item ID
	 * @param  string $itemID       ITM Item ID
	 * @return bool
	 */
	public function exists($vendorID, $vendoritemID, $itemID) {
		return $this->modules->get('XrefVxm')->xref_exists($vendorID, $vendoritemID, $itemID);
	}

	/**
	 * Validate Vendor has Primary X-ref
	 * @param  string $vendorID     Vendor ID
	 * @param  string $vendoritemID Vendor Item ID
	 * @return bool
	 */
	public function vendor_has_primary($vendorID, $itemID) {
		$q = ItemXrefVendorQuery::create();
		$q->filterByItemid($itemID)->filterByVendorid($vendorID);
		$q->filterByPo_ordercode(ItemXrefVendor::POORDERCODE_PRIMARY);
		return boolval($q);
	}
}
