<?php namespace Dplus\Min\Inmain\I2i;
// Dplus Models
use InvItem2ItemQuery, InvItem2Item;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class I2i extends WireData {
	const MODEL              = 'InvItem2Item';
	const MODEL_KEY          = ['parentitemid', 'childitemid'];
	const DESCRIPTION        = 'Item to Item';
	const DESCRIPTION_RECORD = 'Item to Item';
	const RESPONSE_TEMPLATE  = 'Item {key} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'i2i';

	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Return Query
	 * @return InvItem2ItemQuery
	 */
	public function query() {
		return InvItem2ItemQuery::create();
	}

	public function queryI2i($parentID, $childID) {
		$q = $this->query();
		$q->filterByParentitemid($parentID);
		$q->filterByChilditemid($childID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if Record Exists
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return bool
	 */
	public function exists($parentID, $childID) {
		$q = $this->queryI2i($parentID, $childID);
		return boolval($q->count());
	}

	/**
	 * Return InvItem2Item from Database
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function xref($parentID, $childID)  {
		$q = $this->queryI2i($parentID, $childID);
		return $q->findOne();
	}

	/**
	 * Return new InvItem2Item
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function new($parentID = '', $childID = '') {
		$r = new InvItem2Item();
		if ($parentID) {
			$r->setParentitemid($parentID);
		}
		if ($childID) {
			$r->setChilditemid($childID);
		}
		return $r;
	}

	/**
	 * Return Item2Item (new or from DB)
	 * @param  string $parentID  Parent Item ID
	 * @param  string $childID   Child Item ID
	 * @return InvItem2Item
	 */
	public function getOrCreate($parentID, $childID) {
		if ($this->exists($parentID, $childID)) {
			return $this->xref($parentID, $childID);
		}
		return $this->new($parentID, $childID);
	}

/* =============================================================
	Hook Functions
============================================================= */
	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	/**
	 * Return Key for InvItem2Item
	 * @param  InvItem2Item $xref X-Ref
	 * @return string
	 */
	public function getRecordlockerKey(InvItem2Item $xref) {
		return implode(FunctionLocker::glue(), [$xref->parentitemid, $xref->childitemid]);
	}

}
