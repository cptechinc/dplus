<?php namespace Dplus\Mqo\Eqo;

use QuothedQuery, Quothed;

use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Header
 *
 * Handles CRUD operations to the Quote Header
 */
class Header extends WireData  {
	public function __construct() {
		$this->sessionID = session_id();
	}

	public function query() {
		return QuothedQuery::create();
	}

	/**
	 * Returns Editable Quote
	 * @return Quothed
	 */
	public function quote($qnbr) {
		return $this->query()->findOneBySessionidQuote($this->sessionID, $qnbr);
	}

	/**
	 * Return if Editable Quote Header exists
	 * @return bool
	 */
	public function exists($qnbr) {
		return boolval($this->query()->filterBySessionidQuote($this->sessionID, $qnbr)->count());
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Takes Input, processses the action
	 * @param  WireInput $input Input
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'load-quote':
				$this->requestEditableQuote($values->text('qnbr'));
				break;
			case 'edit-quote':
				$this->updateQuote($input);
				break;
			default:
				$this->process_input_itm($input);
				break;
		}
	}

	public function updateQuote(WireInput $input) {
		$values = $input->values();
		$quote = $this->quote($values->text('qnbr'));
		$quote->setShipto_name($values->text('shipto_name'));
		$quote->setShipto_address($values->text('shipto_address'));
		$quote->setShipto_address2($values->text('shipto_address2'));
		$quote->setShipto_city($values->text('shipto_city'));
		$quote->setShipto_state($values->text('shipto_state'));
		$quote->setShipto_zip($values->text('shipto_zip'));
		$quote->setContact($values->text('contact'));
		$quote->setPhone(str_replace('-', '', $values->text('phone')));
		$quote->setFaxnbr(str_replace('-', '', $values->text('fax')));
		$quote->setEmail($values->text('email'));
		$quote->setCustpo($values->text('custpo'));
		$quote->setShipviacd($values->text('shipvia'));
		$quote->setFob($values->text('fob'));
		$quote->setDelivery($values->text('delivery'));
		$quote->setCareof($values->text('careof'));
		$quote->setWarehouse($values->text('warehouse'));
		$saved = $quote->save();
		if ($saved) {
			$this->requestUpdateQuote($quote->quotenumber);
		}
	}



/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	private function requestDplus(array $data) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['default'], $this->sessionID);
	}

	/**
	 * Request Editable Quote header, items
	 * @param  string $qnbr Quote Number
	 * @return void
	 */
	public function requestEditableQuote($qnbr) {
		$data = ['EDITQUOTE', "QUOTENO=$qnbr"];
		$this->requestDplus($data);
	}

	/**
	 * Request Update Quote header
	 * @param  string $qnbr Quote Number
	 * @return void
	 */
	public function requestUpdateQuote($qnbr) {
		$data = ['UPDATEQUOTEHEAD', "QUOTENO=$qnbr"];
		$this->requestDplus($data);
	}
}
