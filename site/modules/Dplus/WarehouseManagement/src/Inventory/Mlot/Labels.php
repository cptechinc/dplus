<?php namespace Dplus\Wm\Inventory\Mlot;
// Dplus Model
use InvLot;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\WireUpload;
// Dplus Codes
use Dplus\Codes\Response;
// Dplus Msa
use Dplus\Msa;
// Dplus Mth
use Dplus\Mth;
// Dplus Warehouse Management
use Dplus\Wm\Base;
use Dplus\Wm\Inventory\Lotm;
use Dplus\Wm\LastPrintJob\Lotlbl as UserPrintJobs;

/**
 * Img
 * Class for uploading Images and tying them to lots
 *
 * @property bool useAutofile Use Dplus Autofiler (otherwise use request methods)
 */
class Labels extends Base {
	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}


/* =============================================================
	Input Processing Functions
============================================================= */
	/**
	 * Process Input, and take action
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function process(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'print-label':
				return $this->inputPrint($input);
				break;
		}
		return false;
	}

	/**
	 * Print Label from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	public function inputPrint(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($values->offsetExists('lotnbr') === false) {
			$this->setResponse(Response::responseError('Lot / Serial Number was not provided'));
			return false;
		}

		return $this->print($input);
	}

	/**
	 * Validate Lot, Print Lot
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function print(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$lotM = Lotm::getInstance();

		if ($lotM->exists($values->text('lotnbr')) === false) {
			$lotnbr = $values->text('lotnbr');
			$this->setResponse(Response::responseError("Lot / Serial Number $lotnbr not found"));
			return false;
		}
		$lot = $lotM->lot($values->text('lotnbr'));
		return $this->printLot($input, $lot);
	}

	/**
	 * Print Lot Label
	 * @param  WireInput $input  Input Data
	 * @param  InvLot    $lot    Lot
	 * @return bool
	 */
	private function printLot(WireInput $input, InvLot $lot) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$data = new WireData();
		$data->lotnbr = $lot->lotnbr;
		$data->qty    = $values->int('qty', ['blankValue' => 1]);

		$prtd = Msa\Prtd::getInstance();
		if ($prtd->exists($values->text('printer')) === false) {
			$id = $values->text('printer');
			$this->setResponse(Response::responseError("Printer $id not found"));
			return false;
		}
		$data->printer = $values->text('printer');

		$tlm = Mth\Tlm::getInstance();
		if ($tlm->exists($values->text('format')) === false) {
			$id = $values->text('format');
			$this->setResponse(Response::responseError("Label Format $id not found"));
			return false;
		}
		$data->format = $values->text('format');
		$this->requestPrint($data);
		$this->setResponse(Response::responseSuccess("Printed Label for $data->lotnbr"));
		return true;
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	private function requestPrint(WireData $rqst) {
		$data = ['LOTLBL', "LOTNBR=$rqst->lotnbr", "PRINTER=$rqst->printer", "FORMAT=$rqst->format", "QTY=$rqst->qty"];
		$this->sendDplusRequest($data);
	}

/* =============================================================
	Response Functions
============================================================= */
	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'mlot-labels', $response);
	}

	/**
	 * Return Session Response
	 * @return Response
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'mlot-labels');
	}

	/**
	 * Delete Session Response
	 */
	public function deleteResponse() {
		$this->wire('session')->removeFor('response', 'mlot-labels');
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public function getUserPrintJobs() {
		return UserPrintJobs::getInstance();
	}
}
