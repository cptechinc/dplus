<?php namespace Dplus\Msa;
// Dplus Models
use MsaSysopCodeQuery, MsaSysopCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

class Sysop extends WireData {
	const MODEL              = 'MsaSysopCode';
	const MODEL_KEY          = 'id';
	const DESCRIPTION        = 'Sysop';
	const RESPONSE_TEMPLATE  = 'Sysop {system} Option {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'sysop';

	const SYSTEMS = [
		'AP', 'AR',
		'IN',
		'MS',
		'SO'
	];

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
	 * @return MsaSysopCodeQuery
	 */
	public function query() {
		return MsaSysopCodeQuery::create();
	}

	/**
	 * Return Query Filtered to System and Code
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return MsaSysopCodeQuery
	 */
	public function queryCode($system, $id) {
		$q = $this->query();
		$q->filterBySystem($system);
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if Option Code Exists
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return bool
	 */
	public function exists($system, $id) {
		$q = $this->queryCode($system, $id);
		return boolval($q->count());
	}

	/**
	 * Return Option Code
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return MsaSysopCode
	 */
	public function code($system, $id) {
		$q = $this->queryCode($system, $id);
		return $q->findOne();
	}

	/**
	 * Return Option Code is a Note
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return bool
	 */
	public function isNote($system, $id) {
		$q = $this->queryCode($system, $id);
		$q->select(MsaSysopCode::aliasproperty('note_code'));
		return boolval($q->findOne());
	}

	/**
	 * Return new MsaSysopCode
	 * @param  string $system  System
	 * @param  string $id      Option Code
	 * @return MsaSysopCode
	 */
	public function new($system, $id) {
		$opt = new MsaSysopCode();
		$opt->setSystem($option);
		$opt->setId($id);
		return $opt;
	}

	/**
	 * Return JSON
	 * @param  MsaSysopCode $opt
	 * @return array
	 */
	public function codeJson(MsaSysopCode $opt) {
		return [
			'system'      => $opt->system,
			'sysop'       => $opt->id,
			'id'          => $opt->id,
			'description' => $opt->description,
			'input' => [
				'validate' => $opt->validate(),
				'force'    => $opt->force(),
				'notetype' => $opt->notecode
			]
		];
	}
}
