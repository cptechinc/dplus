<?php namespace Dplus\Mqo\Eqo;

use QuothedQuery, Quothed;

use ProcessWire\WireData;

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
			default:
				$this->process_input_itm($input);
				break;
		}
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	private function requestDplus(array $data) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
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
}
