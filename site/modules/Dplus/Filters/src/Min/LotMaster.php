<?php namespace Dplus\Filters\Min;
// Dplus Model
use InvLotMaster;
use WhseLotserialQuery, WhseLotserial;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for adding Filters to the InvLotMaster class
 */
class LotMaster extends AbstractFilter {
	const MODEL = 'InvLotMaster';

/* =============================================================
	Abstract Contract / Extensible Functions
============================================================= */
	public function _search($q) {
		$columns = [
			InvLotMaster::aliasproperty('lotserial'),
			InvLotMaster::aliasproperty('itemid'),
		];
		$this->query->search_filter($columns, strtoupper($q));
	}

/* =============================================================
	Base Filter Functions
============================================================= */
	/**
	 * Filter To In Stock Lots
	 */
	public function inStock() {
		$q = WhseLotserialQuery::create();
		$q->select(WhseLotserial::aliasproperty('lotserial'));
		$lotnbrs = $q->find()->toArray();
		$this->query->filterByLotnbr($lotnbrs);
	}

/* =============================================================
	Misc Query Functions
============================================================= */
	/**
	 * Return if Lot Number Exists
	 * @param  string $lotnbr Lot Number
	 * @return bool
	 */
	public function exists($lotnbr) {
		$q = $this->query();
		$q->filterByLotnbr($lotnbr);
		return boolval($q->count());
	}

	/**
	 * Return if Lot with Lot Refernce
	 * @param  string $lotref Lot Refernce
	 * @return bool
	 */
	public function existsLotRef($lotref) {
		$q = $this->query();
		$q->filterByLotref($lotref);
		return boolval($q->count());
	}
}
