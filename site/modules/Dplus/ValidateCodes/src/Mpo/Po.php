<?php namespace Dplus\CodeValidators\Mpo;

use Dplus\CodeValidators\Mpo;
use Dplus\CodeValidators\Map as MapValidator;
use Dplus\CodeValidators\Mar as MarValidator;

use PurchaseOrderQuery, PurchaseOrder;
use ApInvoiceQuery, ApInvoice;

/**
 * Po
 * Class for Validating PO table codes, IDs
 */
class Po extends Mpo {
	/**
	 * Validates VendorID
	 * @param  string $vendorID VendorID
	 * @return bool
	 */
	public function vendorid($vendorID) {
		$validate = new MapValidator();
		return $validate->vendorid($vendorID);
	}

	/**
	 * Validates Vendor Ship-FromID
	 * @param  string $vendorID   VendorID
	 * @param  string $shipfromID Vendor Ship-FromID
	 * @return bool
	 */
	public function shipfromid($vendorID, $shipfromID) {
		$validate = new MapValidator();
		return $validate->vendor_shipfrom($vendorID, $shipfromID);
	}

	/**
	 * Validates VendorID
	 * @param  string $vendorID VendorID
	 * @return bool
	 */
	public function shipvia($shipvia) {
		$validate = new MarValidator();
		return $validate->shipvia($shipvia);
	}

	/**
	 * Validates Terms Code
	 * @param  string $termscode  AP Terms Code
	 * @return bool
	 */
	public function termscode($termscode) {
		$validate = new MapValidator();
		return $validate->termscode($termscode);
	}

	/**
	 * Validates Freight Paid By Code
	 * @param  string $freightpaidby  Freight Paid By Code
	 * @return bool
	 */
	public function freightpaidby($freightpaidby) {
		return array_key_exists($freightpaidby, PurchaseOrder::FREIGHTPAIDBY_DESCRIPTIONS);
	}

	/**
	 * Validates FOB
	 * @param  string $fob  FOB
	 * @return bool
	 */
	public function fob($fob) {
		return array_key_exists($fob, PurchaseOrder::FOB_DESCRIPTIONS);
	}

	/**
	 * Validate Exchange Country Code
	 * @param  string $countrycode  Currency Country Code
	 * @return bool
	 */
	public function exchange_country($countrycode) {
		$validate = new MapValidator();
		return $validate->countrycode($countrycode);
	}

	/**
	 * Validate Status Code
	 * @param  string $status Status Code
	 * @return bool
	 */
	public function status($status) {
		return array_key_exists($status, PurchaseOrder::STATUS_DESCRIPTIONS);
	}
}
