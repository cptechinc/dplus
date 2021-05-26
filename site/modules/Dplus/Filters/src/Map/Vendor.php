<?php namespace Dplus\Filters\Map;
// Dplus Model
use VendorQuery, Vendor as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the VendorQuery class
 */
class Vendor extends AbstractFilter {
	const MODEL = 'Vendor';

/* =============================================================
	1. Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			Model::aliasproperty('vendorid'),
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
	 * @param  string $vendorID Vendor ID
	 * @return Vendor
	 */
	public function getVendor($vendorID) {
		return VendorQuery::create()->findOneByVendorid($vendorID);
	}

	/**
	 * Return if Vendor exists
	 * @param  string $vendorID Vendor ID
	 * @return bool
	 */
	public function exists($vendorID) {
		return boolval(VendorQuery::create()->filterByVendorid($vendorID)->count());
	}

	/**
	 * Return Position of Record in results
	 * @param  string Vendor ID
	 * @return int
	 */
	public function positionById($vendorID) {
		if ($this->exists($vendorID) === false) {
			return 0;
		}
		$v = $this->getVendor($vendorID);
		return $this->position($v);
	}
}
