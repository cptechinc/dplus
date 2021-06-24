<?php namespace Dplus\Min\Inmain\Itm;

use ItmDimensionQuery, ItmDimension;

use ProcessWire\WireData;

class Dimensions extends WireData {
	const MODEL              = 'ItmDimension';
	const MODEL_KEY          = 'itemid';
	const DESCRIPTION        = 'Item Dimensions';
	const DESCRIPTION_RECORD = 'Item Dimensions';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Dimensions {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm';

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Get ItmDimension Record for Item ID
	 * @param  string $itemID Item ID
	 * @return ItmDimension
	 */
	public function getOrCreateDimension($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);

		if ($q->count()) {
			return $q->findOne();
		}
		return $this->newDimension($itemID);
	}

	/**
	 * Return if Item has Itm Dimension Record
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return new ItmDimension
	 * @param  string $itemID Item ID
	 * @return ItmDimension
	 */
	public function newDimension($itemID) {
		$dim = new ItmDimension();
		$dim->setItemid($itemID);
		return $dim;
	}

	/**
	 * Return Query
	 * @return ItmDimensionQuery
	 */
	public function query() {
		return ItmDimensionQuery::create();
	}

	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}
}
