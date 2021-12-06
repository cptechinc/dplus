<?php namespace Dplus\Min\Inmain\Itm;
// Dplus Models
use InvOptCodeQuery, InvOptCode;
use MsaSysopCode, SysopOptionalCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Filters
use Dplus\Filters;
// Dplus Msa
use Dplus\Msa\Sysop;
use Dplus\Msa\SysopOptions;
// Dplus Itm
use Dplus\Min\Inmain\Itm\Options as ItmOptions;

/**
 * Options
 * Manages CRUD operations for the InvOptCode Records
 */
class Options extends WireData {
	const SYSTEM = 'IN';

	public function __construct() {
		$this->sessionID = session_id();
		$this->codes  = ItmOptions\Codes::getInstance();
		$this->qnotes = ItmOptions\Qnotes::getInstance();
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
	Input Functions
============================================================= */
	/**
	 * Process Input Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$sysop = $this->getSysop();

		if ($sysop->isNote('IN', $values->text('sysop'))) {
			return	$this->qnotes->processInput($input);
		}
		return	$this->codes->processInput($input);
	}

/* =============================================================
	Response Functions
============================================================= */
	/**
	 * Get Session Response
	 * @return Response|null
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'itm-options');
	}

	/**
	 * Delete Response
	 * @return void
	 */
	public function deleteResponse() {
		return $this->wire('session')->removeFor('response', 'itm-options');
	}

	/**
	 * Return if Field has Error
	 * NOTE: Uses $session->response_itm->fields to derive this
	 * @param  string $inputname Input name e.g. commissiongroup
	 * @return bool
	 */
	public function fieldHasError($inputname) {
		$response = $this->getResponse();
		return ($response) ? array_key_exists($inputname, $response->fields) : false;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Return if Item ID is missing Required Codes
	 * @param  string $itemID Item ID
	 * @return bool           Return True for invalid
	 */
	public function isMissingRequiredCodes($itemID) {
		$sysop = $this->getSysop();
		$filter = new Filters\Msa\MsaSysopCode();
		$filter->system(self::SYSTEM);
		$filter->query->filterById($sysop->getRequiredCodes(self::SYSTEM));
		$codes = $filter->query->find();

		foreach ($codes as $code) {
			if ($code->isNote()) {
				if ($this->qnotes->notesExist($itemID, $code->notecode) === false) {
					return true;
				}
				continue;
			}

			if ($this->codes->exists($itemID, $code->id) === false) {
				return true;
			}
		}
		return false;
	}

	public function getSysop() {
		return Sysop::getInstance();
	}

	public function getSysopOptions() {
		return SysopOptions::getInstance();
	}
}
