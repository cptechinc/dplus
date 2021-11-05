<?php namespace Dplus\Msa;
// Dplus Models
use SysopOptionalCodeQuery, SysopOptionalCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class SysopOptions extends WireData {
	const MODEL              = 'SysopOptionalCode';
	const MODEL_KEY          = 'system,sysop,code';
	const TABLE              = 'sys_opt_optcode';
	const DESCRIPTION        = 'Sysop Option';
	const RESPONSE_TEMPLATE  = 'Sysop {system} Option {sysop} {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sysop';

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

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return SysopOptionalCodeQuery
	 */
	public function query() {
		return SysopOptionalCodeQuery::create();
	}

	/**
	 * Return Query Filtered to System and Code
	 * @param  string $system  System
	 * @param  string $sysop   System Option Code
	 * @param  string $code    Option ID
	 * @return SysopOptionalCodeQuery
	 */
	public function queryCode($system, $sysop, $code) {
		$q = $this->query();
		$q->filterBySystem($system);
		$q->filterBySysop($sysop);
		$q->filterById($code);
		return $q;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if Option Code Exists
	 * @param  string $system  System
	 * @param  string $sysop   System Option Code
	 * @param  string $code    Option ID
	 * @return bool
	 */
	public function exists($system, $sysop, $code) {
		$q = $this->queryCode($system, $sysop, $code);
		return boolval($q->count());
	}

	/**
	 * Return Option Code
	 * @param  string $system  System
	 * @param  string $sysop   System Option Code
	 * @param  string $code    Option ID
	 * @return SysopOptionalCode
	 */
	public function code($system, $sysop, $code) {
		$q = $this->queryCode($system, $sysop, $code);
		return $q->findOne();
	}

	/**
	 * Return new SysopOptionalCode
	 * @param  string $system  System
	 * @param  string $sysop   System Option Code
	 * @param  string $code    Option ID
	 * @return SysopOptionalCode
	 */
	public function new($system, $sysop, $code) {
		$opt = new SysopOptionalCode();
		$opt->setSystem($system);
		$opt->setSysop($sysop);
		$opt->setId($code);
		return $opt;
	}
}
