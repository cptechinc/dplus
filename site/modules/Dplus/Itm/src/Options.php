<?php namespace Dplus\Min\Inmain\Itm;
// Dplus Models
use InvOptCodeQuery, InvOptCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Options extends WireData {
	const MODEL              = 'InvOptCode';
	const MODEL_KEY          = 'itemid, id';
	const DESCRIPTION        = 'Item Options';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Option {opt} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm';

	public function __construct() {
		$this->sessionID = session_id();
	}

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			$instance->init();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Get InvOptCode Record for Item ID, System Option
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return InvOptCode
	 */
	public function getOrCreateDimension($itemID, $sysop) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterBySysop($sysop);
		if ($q->count()) {
			return $q->findOne();
		}
		return $this->new($itemID, $sysop);
	}

	/**
	 * Return if Item has Itm Dimension Record
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return bool
	 */
	public function exists($itemID, $sysop) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterBySysop($sysop);
		return boolval($q->count());
	}

	public function code($itemID, $sysop) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterBySysop($sysop);
		return $q->findOne();
	}

	/**
	 * Return new InvOptCode
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return InvOptCode
	 */
	public function new($itemID, $option) {
		$opt = new InvOptCode();
		$opt->setItemid($itemID);
		if ($option) {
			$opt->setSysop($option);
		}
		return $opt;
	}

	/**
	 * Return Query
	 * @return InvOptCodeQuery
	 */
	public function query() {
		return InvOptCodeQuery::create();
	}

	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {

	}

}
