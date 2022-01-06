<?php namespace Dplus\Wm\Inventory;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
// Dplus Models
use InvLotQuery, InvLot;
// ProcessWire
use ProcessWire\WireData;

/**
 * Lotm
 */
class Lotm extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Setter Functions
============================================================= */


/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return InvLotQuery
	 */
	public function query() {
		$q = InvLotQuery::create();
		return $q;
	}

	/**
	 * Return Query Filtered By Warehouse ID if set
	 * @param array|string $lotnbr
	 * @return InvLotQuery
	 */
	public function queryLotnbr($lotnbr = null) {
		$q = $this->query();

		if (empty($lotnbr) === false) {
			$q->filterByLotnbr($lotnbr);
		}
		return $q;
	}

/* =============================================================
	Validate, Read Functions
============================================================= */
	/**
	 * Return if Lot Exists
	 * @param  string $lotnbr
	 * @return bool
	*/
	public function exists($lotnbr) {
		$q = $this->queryLotnbr($lotnbr);
		return boolval($q->count());
	}

	/**
	 * Return Lot
	 * @param  string $lotnbr
	 * @return bool
	*/
	public function lot($lotnbr) {
		$q = $this->queryLotnbr($lotnbr);
		return $q->findOne();
	}

/* =============================================================
	Lookup Functions
============================================================= */
	/**
	 * Return Lot(s) have Images associated with them
	 * @param array|string $lotnbr
	 * @return bool
	 */
	public function lotsHaveImages($lotnbr = null) {
		$q = $this->queryLotnbr($lotnbr);
		$q->filterByHasimage(InvLot::YN_TRUE);
		return boolval($q->count());
	}
}
