<?php namespace Dplus\Msa;
// Dplus Printers
use PrinterQuery, Printer;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Prtd extends WireData {
	const MODEL              = 'Printer';
	const MODEL_KEY          = 'id';
	const TABLE              = 'syslogin';
	const DESCRIPTION        = 'Printer';
	const RESPONSE_TEMPLATE  = 'Printer {id} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'prtd';

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
	 * @return PrinterQuery
	 */
	public function query() {
		return PrinterQuery::create();
	}

	/**
	 * Return Query Filtered to Printer ID
	 * @param  string $id    Printer ID
	 * @return PrinterQuery
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
	 * Return if Printer ID Exists
	 * @param  string $id    Printer ID
	 * @return bool
	 */
	public function exists($id) {
		$q = $this->queryId($id);
		return boolval($q->count());
	}

	/**
	 * Return if Printer ID Exists
	 * @param  string $id    Printer ID + Pitch
	 * @return bool
	 */
	public function existsPrinterPitch($id) {
		foreach (Printer::PITCHES as $pitch) {
			$regex = "/\w($pitch)/";

			if (preg_match($regex, $id)) {
				$printerID = str_replace($pitch, '', $id);

				if ($this->exists($printerID) === false) {
					return false;
				}
				$alias = "pitch$pitch";
				$col = Printer::aliasproperty($alias);
				$filter = "filterBy" . ucFirst($col);
				$q = $this->query();
				$q->filterById($printerID);
				$q->$filter(Printer::PITCH_TRUE);
				return boolval($q->count());
			}
		}
		return false;
	}

	/**
	 * Return Printer ID by Printer ID + Pitch
	 * @param  string $id    Printer ID + Pitch
	 * @return bool
	 */
	public function idByPrinterPitch($id) {
		foreach (Printer::PITCHES as $pitch) {
			$regex = "/\w($pitch)/";

			if (preg_match($regex, $id)) {
				$printerID = str_replace($pitch, '', $id);
				return $this->exists($printerID);
			}
		}
		return false;
	}


	/**
	 * Return Printer
	 * @param  string $id    Printer ID
	 * @return Printer
	 */
	public function printer($id) {
		$q = $this->queryId($id);
		return $q->findOne();
	}

	/**
	 * Return new Printer
	 * @param  string $id    Printer ID
	 * @return Printer
	 */
	public function new($id) {
		$opt = new Printer();
		$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('id', 'maxlength')]);
		if ($id != 'new') {
			$opt->setId($id);
		}
		return $opt;
	}

	/**
	 * Return new or Existing Printer
	 * @param  string $id Printer ID
	 * @return Printer
	 */
	public function getOrCreate($id) {
		if ($this->exists($id)) {
			return $this->user($id);
		}
		if ($this->existsPrinterPitch($id)) {
			return $this->user($this->idByPrinterPitch($id));
		}
		return $this->new($id);
	}
}
