<?php namespace Dplus\Filters\Map;

use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
use Dplus\Filters\AbstractFilter;

use VendorQuery, Vendor as Model;

class Vendor extends AbstractFilter {
	const MODEL = 'Vendor';

/* =============================================================
	Abstract Contract Functions
============================================================= */
	public function initQuery() {
		$this->query = VendorQuery::create();
	}

	public function _search($q) {
		$columns = [
			Model::get_aliasproperty('vendorid'),
			Model::get_aliasproperty('name'),
			Model::get_aliasproperty('address'),
			Model::get_aliasproperty('address2'),
			Model::get_aliasproperty('city'),
			Model::get_aliasproperty('state'),
			Model::get_aliasproperty('zip'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return Position of Vendor in results
	 * @param  Model $v Vendor
	 * @return int
	 */
	public function position(Model $v) {
		$vendors = $this->query->find();
		return $people->search($v);
	}

	/**
	 * Return Vendor
	 * @param  string $vendorID Vendor ID
	 * @return Vendor
	 */
	public function get_vendor($vendorID) {
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

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter Query by VendorID
	 * @param  array|string $vendorID  Vendor ID(s)
	 * @return void
	 */
	public function vendorid($vendorID) {
		$this->query->filterByVendorid($vendorID);
	}
}
