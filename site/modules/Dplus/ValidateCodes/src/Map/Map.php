<?php namespace Dplus\CodeValidators;

use ProcessWire\WireData;

use ApTermsCodeQuery, ApTermsCode;
use ApBuyerQuery, ApBuyer;
use CountryCodeQuery, CountryCode;
use VendorQuery, Vendor;
use VendorShipfromQuery, VendorShipfrom;

/**
 * Map
 *
 * Class for Validating AP table codes, IDs, X-refs
 */
class Map extends WireData {
	/**
	 * Return if Vendor ID exists
	 * @param  string $vendorID  Vendor ID
	 * @return bool
	 */
	public function vendorid($vendorID) {
		$q = VendorQuery::create();
		$q->filterByVendorid($vendorID);
		return boolval($q->count());
	}

	/**
	 * Return if Vendor ID exists
	 * @param  string $vendorID  Vendor ID
	 * @return bool
	 */
	public function vendor_shipfrom($vendorID, $shipfromID) {
		$q = VendorShipfromQuery::create();
		$q->filterByVendorid($vendorID);
		$q->filterByShipfromid($shipfromID);
		return boolval($q->count());
	}

	/**
	 * Return if AP Terms Code Exists
	 * @param  string $code AP Terms Code
	 * @return bool
	 */
	public function termscode($code) {
		$q = ApTermsCodeQuery::create();
		$q->filterByCode($code);
		return boolval($q->count());
	}

	/**
	 * Return if Country Code Exists
	 * @param  string $code Country Code
	 * @return bool
	 */
	public function countrycode($code) {
		$q = CountryCodeQuery::create();
		$q->filterByCode($code);
		return boolval($q->count());
	}

	/**
	 * Return if AP Buyer Code Exists
	 * @param  string $code CAP Buyer Code
	 * @return bool
	 */
	public function buyercode($code) {
		$q = ApBuyerQuery::create();
		$q->filterByCode($code);
		return boolval($q->count());
	}
}
