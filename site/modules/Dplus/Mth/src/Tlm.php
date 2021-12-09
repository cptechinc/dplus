<?php namespace Dplus\Mth;
// Dplus ThermalLabelFormats
use ThermalLabelFormatQuery, ThermalLabelFormat;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Tlm extends WireData {
	const MODEL              = 'ThermalLabelFormat';
	const MODEL_KEY          = 'id';
	const TABLE              = 'syslogin';
	const DESCRIPTION        = 'Label Format';
	const RESPONSE_TEMPLATE  = 'Label Format {id} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'tlm';

	const FIELD_ATTRIBUTES = [
		'id' => ['type' => 'text', 'maxlength' => 10],
	];

	public function __construct() {
		$this->sessionID = session_id();

		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
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
	Field Configs
============================================================= */
	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, static::FIELD_ATTRIBUTES) === false) {
			return false;
		}
		if (array_key_exists($attr, static::FIELD_ATTRIBUTES[$field]) === false) {
			return false;
		}
		return static::FIELD_ATTRIBUTES[$field][$attr];
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query
	 * @return ThermalLabelFormatQuery
	 */
	public function query() {
		return ThermalLabelFormatQuery::create();
	}

	/**
	 * Return Query Filtered to ThermalLabelFormat ID
	 * @param  string $id    ThermalLabelFormat ID
	 * @return ThermalLabelFormatQuery
	 */
	public function queryId($id) {
		$q = $this->query();
		$q->filterById($id);
		return $q;
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if ThermalLabelFormat ID Exists
	 * @param  string $id    ThermalLabelFormat ID
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->queryId($id);
		return boolval($q->count());
	}

	/**
	 * Return ThermalLabelFormat
	 * @param  string $id    ThermalLabelFormat ID
	 * @return ThermalLabelFormat
	 */
	public function label($id) {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new ThermalLabelFormat
	 * @param  string $id    ThermalLabelFormat ID
	 * @return ThermalLabelFormat
	 */
	public function new($id) {
		$opt = new ThermalLabelFormat();
		$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		if ($id != 'new') {
			$opt->setId($id);
		}
		return $opt;
	}

	/**
	 * Return new or Existing ThermalLabelFormat
	 * @param  string $id ThermalLabelFormat ID
	 * @return ThermalLabelFormat
	 */
	public function getOrCreate($id) {
		if ($this->exists($id)) {
			return $this->user($id);
		}
		if ($this->existsThermalLabelFormatPitch($id)) {
			return $this->user($this->idByThermalLabelFormatPitch($id));
		}
		return $this->new($id);
	}
}
