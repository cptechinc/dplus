<?php namespace Dplus\Min\Inmain\Itm;
// Dplus Models
use InvOptCodeQuery, InvOptCode;
use MsaSysopCode, SysopOptionalCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
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

// 	/**
// 	 * Update Itm Dimension, Itm Data
// 	 * @param  WireInput $input Input Data
// 	 * @return void
// 	 */
// 	private function updateInput(WireInput $input) {
// 		$rm = strtolower($input->requestMethod());
// 		$values = $input->$rm;
//
// 		$itm = $this->wire('modules')->get('Itm');
// 		$itemID = $values->text('itemID');
//
// 		if ($itm->exists($itemID) === false) {
// 			return false;
// 		}
//
// 		if ($itm->lockrecord($itemID) === false) {
// 			return false;
// 		}
// 		return $this->updateInputCode($input);
// 	}
//
// 	/**
// 	 * Update Itm Dimension, Itm Data
// 	 * @param  WireInput $input Input Data
// 	 * @return void
// 	 */
// 	private function deleteInput(WireInput $input) {
// 		$rm = strtolower($input->requestMethod());
// 		$values = $input->$rm;
//
// 		$itm = $this->wire('modules')->get('Itm');
// 		$itemID = $values->text('itemID');
//
// 		if ($itm->exists($itemID) === false) {
// 			return false;
// 		}
//
// 		if ($itm->lockrecord($itemID) === false) {
// 			return false;
// 		}
// 		return $this->deleteInputCode($input);
// 	}
//

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	protected function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'itm-options', $response);
	}

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
	public function getSysop() {
		return Sysop::getInstance();
	}

	public function getSysopOptions() {
		return SysopOptions::getInstance();
	}
}
