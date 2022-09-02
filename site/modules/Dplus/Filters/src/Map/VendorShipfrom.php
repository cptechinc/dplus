<?php namespace Dplus\Filters\Map;
// Dplus Model
use VendorShipfromQuery, VendorShipfrom as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the VendorShipfromQuery class
 */
class VendorShipfrom extends AbstractFilter {
	const MODEL = 'VendorShipfrom';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q, $cols = []) {
		$columns = [
			Model::aliasproperty('name'),
			Model::aliasproperty('address'),
			Model::aliasproperty('address2'),
			Model::aliasproperty('city'),
			Model::aliasproperty('state'),
			Model::aliasproperty('zip'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter Query by VendorID
	 * @param  array|string $vendorID  Vendor ID(s)
	 * @return void
	 */
	public function vendorid($vendorID) {
		$this->query->filterByVendorid($vendorID);
	}

/* =============================================================
	3. Input Filter Functions
============================================================= */

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return Vendor
	 * @param  string $vendorID   Vendor ID
	 * @param  string $shipfromID Vendor Ship-From ID
	 * @return Vendor
	 */
	public function getVendorShipfrom($vendorID, $shipfromID) {
		$q = $this->query();
		$q->filterByVendorid($vendorID);
		$q->filterByVendorShipfromid($shipfromID);
		return $q->findOne();
	}

	/**
	 * Return if Vendor Ship-From exists
	 * @param  string $vendorID   Vendor ID
	 * @param  string $shipfromID Vendor Ship-From ID
	 * @return bool
	 */
	public function exists($vendorID, $shipfromID) {
		$q = $this->query();
		$q->filterByVendorid($vendorID);
		$q->filterByVendorShipfromid($shipfromID);
		return boolval($q->count());
	}

	/**
	 * Return Position of Record in results
	 * @param  string $vendorID   Vendor ID
	 * @param  string $shipfromID Vendor Ship-From ID
	 * @return int
	 */
	public function positionById($vendorID, $shipfromID) {
		if ($this->exists($vendorID, $shipfromID) === false) {
			return 0;
		}
		$v = $this->getVendorShipfrom($vendorID, $shipfromID);
		return $this->position($v);
	}
}
