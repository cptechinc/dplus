<?php namespace Dplus\Mpm\Pmmain\Bmm;
// Dplus Models
use BomComponentQuery, BomComponent;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Components extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
	}

/* =============================================================
	Queries
============================================================= */
	/**
	 * Return Query
	 * @return BomComponentQuery
	 */
	public function query() {
		return BomComponentQuery::create();
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponentQuery
	 */
	public function queryComponent($bomID, $componentID) {
		$q = $this->query();
		$q->filterByProduces($bomID);
		$q->filterByItemid($componentID);
		return $q;
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @return BomComponentQuery
	 */
	public function queryBomId($bomID) {
		$q = $this->query();
		$q->filterByProduces($bomID);
		return $q;
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return If BomComponent Exists
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return bool
	 */
	public function component($bomID, $componentID) {
		$q = $this->queryComponent($bomID, $componentID);
		return $q->findOne();
	}


	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @return array
	 */
	public function getComponentIds($bomID) {
		$q = $this->queryBomId($bomID);
		$q->select(BomComponent::aliasproperty('itemid'));
		return $q->find()->toArray();
	}

	/**
	 * Return BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return bool
	 */
	public function exists($bomID, $componentID) {
		$q = $this->queryComponent($bomID, $componentID);
		return boolval($q->count());
	}

	/**
	 * Return if Components Exist for BoM
	 * @param  string $bomID       Bom Item ID
	 * @return bool
	 */
	public function hasComponents($bomID) {
		$q = $this->queryBomId($bomID);
		return boolval($q->count());
	}

/* =============================================================
	CRUD Create
============================================================= */
	/**
	 * Return BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponent
	 */
	public function new($bomID, $componentID) {
		$componentID = $componentID != 'new' ? $componentID : '';
		$c = new BomComponent();
		$c->setProduces($bomID);
		$c->setItemid($componentID);
		$c->setScrap('N');
		$q->setQty(0);
		return $c;
	}

	/**
	 * Return New or Existing BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponent
	 */
	public function getOrCreate($bomID, $componentID) {
		if ($this->exists($bomID, $componentID)) {
			return $this->component($bomID, $componentID);
		}
		return $this->new($bomID, $componentID);
	}
}
