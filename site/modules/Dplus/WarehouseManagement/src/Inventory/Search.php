<?php namespace Dplus\Wm\Inventory;

use InvsearchQuery, Invsearch;

use ProcessWire\WireData;

// Dplus Databases
use Dplus\Databases\Connectors\Dpluso as DbDpluso;

/**
 * Search
 * Class for filtering Inventory using InvsearchQuery
 */
class Search extends WireData {
	private static $instance;

	public function __construct() {
		$this->sessionID = session_id();
	}

	public static function getInstance() {
		return self::instance();
	}

	public static function instance() {
		if (empty(self::$instance)) {
			self::$instance = new self();
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return InvsearchQuery
	 */
	public function query() {
		$q = InvsearchQuery::create();
		$q->filterBySessionid($this->sessionID);
		return $q;
	}

/* =============================================================
	Count Functions
============================================================= */
	/**
	 * Return number of all records
	 * @return int
	 */
	public function countAll() {
		$q = $this->query();
		return $q->count();
	}

	/**
	 * Return the Number of Distinct Item IDs
	 * @param  string $binID ** Optional, Bin ID to narrow down
	 * @return int
	 */
	public function countItemidsDistinct($binID = '') {
		$q = $this->query();
		$q->addAsColumn('count', 'COUNT(DISTINCT(itemid))');
		$q->select('count');
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return intval($q->findOne());
	}

	/**
	 * Return the Number of Distinct Lotserials
	 * @param  string $binID ** Optional, Bin ID to narrow down
	 * @return int
	 */
	public function countLotserialsDistinct($binID = '') {
		$q = $this->query();
		$q->addAsColumn('count', 'COUNT(DISTINCT(lotserial))');
		$q->select('count');
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return intval($q->findOne());
	}

	/**
	 * Return the number of Invsearch records for this Item ID, Bin
	 * @param  string $itemID Item ID
	 * @param  string $binID  ** Optional ***, Bin ID to narrow down
	 * @return int
	 */
	public function countItemidRecords($itemID, $binID = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);
		if (!empty($binID)) {
			$q->filterBy('Bin', $binID);
		}
		return $q->count();
	}

	/**
	 * Return the Number of Lotserials for Item ID
	 * @param  string $itemID Item ID
	 * @param  string $binID  ** Optional ***, Bin ID to narrow down
	 * @return int
	 */
	public function countLotserialsItemid($itemID, $binID = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->addAsColumn('count', 'COUNT(DISTINCT(lotserial))');
		$q->select('count');

		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return intval($q->findOne());
	}

	/**
	 * Return the number of records for Lotserial
	 * @param  string $lotserial Lot Number / Serial Number
	 * @param  string $binID     Bin ID
	 * @return int
	 */
	public function countLotserialRecords($lotserial, $binID = '') {
		$q = $this->query();
		$q->filterByLotserial($lotserial);
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return $q->count();
	}

	/**
	 * Return the number of Bins this itemID is found at
	 * @param  string $itemID
	 * @return int
	 */
	public function countBinsItemid($itemID) {
		$q = $this->query();
		$q->addAsColumn('count', 'COUNT(DISTINCT(bin))');
		$q->select('count');
		$q->filterByQty(array('min' => 0));
		$q->filterByItemid($itemID);
		return intval($q->findOne());
	}

	/**
	 * Return the number of Bins this item is found at
	 * @param  InvSearch $item Item to find
	 * @return int
	 */
	public function countItemBins(InvSearch $item) {
		$q = $this->query();
		$q->addAsColumn('count', 'COUNT(DISTINCT(bin))');
		$q->select('count');
		$q->filterByQty(array('min' => 0));
		$q->filterByItemid($item->itemid);

		if ($item->is_lotted() || $item->is_serialized()) {
			$q->filterByLotserial($item->lotserial);
		}
		return intval($q->findOne());
	}

	/**
	 * Return the number of Bins this item is found at
	 * @param  InvSearch $item Item to find
	 * @return int
	 */
	public function getItemBins(InvSearch $item) {
		$q = $this->query();
		$q->addAsColumn('count', 'COUNT(DISTINCT(bin))');
		$q->select('count');
		$q->filterByQty(array('min' => 0));
		$q->filterByItemid($item->itemid);

		if ($item->is_lotted() || $item->is_serialized()) {
			$q->filterByLotserial($item->lotserial);
		}
		return $q->find();
	}

/* =============================================================
	GET Functions
============================================================= */
	/**
	 * Return Inventory Items that are distinct itemids
	 * @param  string $binID *** Optional ***l, Bin ID to narrow down
	 * @return Invsearch[]|ObjectCollection
	 */
	public function getItemsDistinct($binID = '') {
		$q = $this->query();
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		$q->groupBy('Itemid');
		return $q->find();
	}

	/**
	 * Return  ItemIDs
	 * @param  string $binID
	 * @return array
	 */
	public function getDistinctItemids($binID = '') {
		$q = $this->query();
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		$q->withColumn('DISTINCT(Itemid)', 'itemid');
		$q->select('itemid');
		return $q->find()->toArray();
	}

	/**
	 * Return one Invsearch filter bty item ID
	 * @param  string $itemID Item ID
	 * @param  string $binID  ** Optional ***, Bin ID to narrow down
	 * @return int
	 */
	public function getInvsearchByItemid($itemID, $binID = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return $q->findOne();
	}

	/**
	 * Return Inventory LotSerial Records for Item ID
	 * @param  string $itemID  Item ID
	 * @param  string $binID   *** Optional ***, Bin ID to narrow down
	 * @param  string $orderby Property to Order By
	 * @return Invsearch[]|ObjectCollection
	 */
	public function getLotserialsItemid($itemID, $binID = '', $orderby = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);

		if (!empty($binID)) {
			$q->filterByBin($binID);
		}

		if (!empty($orderby)) {
			$q->orderBy($orderby);
		}
		return $q->find();
	}

	/**
	 * Return if Lotserial Exists
	 * @param  string $lotserial Lot / Serial number
	 * @param  string $binID     Bin ID
	 * @return bool
	 */
	public function lotserialExists($lotserial, $binID = '') {
		$q = $this->query();
		$q->filterByLotserial($lotserial);
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return boolval($q->count());
	}

	/**
	 * Return One Invsearch Record filtered by Lotserial
	 * @param  string $lotserial Lot / Serial number
	 * @param  string $binID     Bin ID
	 * @return Invsearch
	 */
	public function getLotserial($lotserial, $binID = '') {
		$q = $this->query();
		$q->filterByLotserial($lotserial);
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return $q->findOne();
	}

	/**
	 * Return Inventory LotSerial Records for Item ID
	 * @param  string $itemID  Item ID
	 * @param  string $binID   *** Optional ***, Bin ID to narrow down
	 * @param  string $orderby Property to Order By
	 * @return Invsearch[]|ObjectCollection
	 */
	public function getLotserialsDistinctByItemid($itemID, $binID = '', $orderby = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);

		if (!empty($binID)) {
			$q->filterByBin($binID);
		}

		if (!empty($orderby)) {
			$q->orderBy($orderby);
		}
		$q->groupBy('Lotserial');
		return $q->find();
	}

	public function getDistinctLotserialsArray() {
		$q = $this->query();
		$q->select(Invsearch::get_aliasproperty('lotserial'));
		$q->distinct();
		return $q->find()->toArray();
	}

/* =============================================================
	GET QTY Functions
============================================================= */
	/**
	 * Get Total Qty for Item ID
	 * @param  string $itemID Item ID
	 * @param  string $binID  Bin ID
	 * @return float
	 */
	public function getQtyItemid($itemID, $binID = '') {
		$q = $this->query();
		$q->addAsColumn('qty', 'SUM(qty)');
		$q->select('qty');
		$q->filterBySessionid($this->sessionID);
		$q->filterByItemid($itemID);

		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return $q->findOne();
	}

	/**
	 * Get Qty by Item, and filter to lotserial if needed
	 * @param  Invsearch $item
	 * @param  string    $binID Bin ID
	 * @return float
	 */
	public function getQtyInvsearch(Invsearch $item, $binID = '') {
		$q = $this->query();
		$q->addAsColumn('qty', 'SUM(qty)');
		$q->select('qty');
		$q->filterBySessionid($this->sessionID);
		$q->filterByItemid($item->itemid);
		if ($item->lotserial) {
			$q->filterByLotserial($item->lotserial);
		}

		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		return $q->findOne();
	}

/* =============================================================
	GET BINS Functions
============================================================= */
	/**
	 * Return Invsearch Records for Item ID grouped by Bin ID
	 * @param  string $itemID Item ID
	 * @return Invsearch[]|ObjectCollection
	 */
	public function getBinsItemid($itemID, $binID = '') {
		$q = $this->query();
		$q->filterByItemid($itemID);
		if (!empty($binID)) {
			$q->filterByBin($binID);
		}
		$q->withColumn('SUM(qty)', 'qty');
		$q->groupBy('Bin');
		return $q->find();
	}

	/**
	 * Request Search
	 * @param  string $q Query
	 * @return void
	 */
	public function requestSearch($q) {
		$dplusdb = DbDpluso::instance()->dbconfig->dbName;;
		$q = strtoupper($q);
		$data = array("DBNAME=$dplusdb", 'INVSEARCH', "QUERY=$q");
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($this->wire('config')->cgis['warehouse'], $this->sessionID);
	}
}
