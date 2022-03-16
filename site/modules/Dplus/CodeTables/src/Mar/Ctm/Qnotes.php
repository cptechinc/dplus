<?php namespace Dplus\Codes\Mar\Ctm;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes as QnotesNs;

class Qnotes extends WireData {
	protected static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->ictp = QnotesNs\CustType\Ictp::instance();
		$this->kctp = QnotesNs\CustType\Kctp::instance();
		$this->pctp = QnotesNs\CustType\Pctp::instance();
		$this->sctp = QnotesNs\CustType\Sctp::instance();
	}

	public function getQnotes() {
		return [$this->ictp, $this->kctp, $this->pctp, $this->sctp];
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Type, send Input Data to Qnotes CRUD by type
	 * @param  WireInput $input Input Data
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('type')) {
			case 'ICTP':
				$this->ictp->processInput($input);
				break;
			case 'KCTP':
				$this->kctp->processInput($input);
				break;
			case 'PCTP':
				$this->pctp->processInput($input);
				break;
			case 'SCTP':
				$this->sctp->processInput($input);
				break;
		}
	}

/* =============================================================
	Responses
============================================================= */
	/**
	 * Return Responses
	 * @return array[QnotesNs\Response]
	 */
	public function getResponses() {
		$responses = [];

		foreach ($this->getQnotes() as $qnotes) {
			$response = $qnotes->getResponse();

			if (empty($response)) {
				continue;
			}
			$responses[] = $response;
		}
		return $responses;
	}

	/**
	 * Delete Respnoses
	 * @return void
	 */
	public function deleteResponses() {
		foreach ($this->getQnotes() as $qnotes) {
			$qnotes->deleteResponse();
		}
	}
}
