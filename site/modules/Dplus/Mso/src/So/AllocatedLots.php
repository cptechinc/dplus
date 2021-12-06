<?php namespace Dplus\Mso\So;
// Dplus Model
use SoAllocatedLotserialQuery, SoAllocatedLotserial;
// ProcessWire
use ProcessWire\WireData;
// Dplus Document Management
use Dplus\DocManagement\Finders\Lt\Img as Docm;

class AllocatedLots extends WireData {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query filtered By Sessionid
	 * @return SoAllocatedLotserialQuery
	 */
	public function query() {
		return SoAllocatedLotserialQuery::create();
	}

	/**
	 * Return Query Filtered by Sales Order Number
	 * @param  string $ordn  Sales Order Number
	 * @return SoAllocatedLotserialQuery
	 */
	public function querySo($ordn) {
		$q = $this->query();
		$q->filterByOrdn($ordn);
		return $q;
	}

	/**
	 * Return Query Filtered by Sales Order Number, Linenumber
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return SoAllocatedLotserialQuery
	 */
	public function querySoLinenbr($ordn, $linenbr) {
		$q = $this->querySo($ordn);
		$q->filterByLine($linenbr);
		return $q;
	}

/* =============================================================
	Read Functions
============================================================= */
	/**
	 * Return if Order Line has Allocated Lotserials
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return bool
	 */
	public function hasAllocated($ordn, $linenbr) {
		$q = $this->querySoLinenbr($ordn, $linenbr);
		return boolval($q->count());
	}

	/**
	 * Return Allocated Lotserials for Order
	 * @param  string $ordn     Sales Order Number
	 * @param  string $linenbr  Line Number
	 * @return SoAllocatedLotserial[]
	 */
	public function allocatedLotserials($ordn, $linenbr) {
		if ($this->hasAllocated($ordn, $linenbr) === false) {
			return [];
		}
		$q = $this->querySoLinenbr($ordn, $linenbr);
		return $q->find();
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public function getDocm() {
		return new Docm();
	}
}
