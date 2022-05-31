<?php namespace Dplus\Codes\Min\Iwhm;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes as QnotesNs;

/**
 * Iwhm\Qnotes
 * Wrapper for the Qnotes types
 */
class Qnotes extends WireData {
	/** @var self */
	protected static $instance;

	/**
	 * Return Instance
	 * @return self
	 */
	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	public function __construct() {
		$this->iwhs = QnotesNs\Iwhs::instance();
		$this->swhs = QnotesNs\Swhs::instance();
	}

	/**
	 * Return the different Qnotes Types
	 * @return array
	 */
	public function getQnotes() {
		return [$this->iwhs, $this->swhs];
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
			case 'IWHS':
				$this->iwhs->processInput($input);
				break;
			case 'SWHS':
				$this->swhs->processInput($input);
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
