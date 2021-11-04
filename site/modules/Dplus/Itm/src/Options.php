<?php namespace Dplus\Min\Inmain\Itm;
// Dplus Models
use InvOptCodeQuery, InvOptCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Msa
use Dplus\Msa\Sysop;

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
			self::$instance = $instance;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return InvOptCodeQuery
	 */
	public function query() {
		return InvOptCodeQuery::create();
	}

	/**
	 * Return Array ready for JSON
	 * @param  InvOptCode  $code Code
	 * @return array
	 */
	public function codeJson($sysop, InvOptCode $code = null) {
		$code = empty($code) === false ? $code : $this->new('', $sysop);
		return [
			'sysop'       => $code->sysop,
			'code'        => $code->code,
			'description' => $code->description
		];
	}

/* =============================================================
	Create, Read Functions
============================================================= */
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

	/**
	 * Return Option Code
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return InvOptCode
	 */
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
	public function new($itemID, $sysop) {
		$opt = new InvOptCode();
		$opt->setItemid($itemID);
		if ($sysop) {
			$opt->setSysop($sysop);
		}
		return $opt;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public function getSysop() {
		return Sysop::getInstance();
	}
}
