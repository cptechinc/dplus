<?php namespace Dplus\Mqo\Eqo;
// Dpluso Models
use QuotdetQuery, Quotdet;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;

/**
 * Items
 *
 * Handles CRUD operations to Quote Items
 */
class Items extends WireData  {
	public function __construct() {
		$this->sessionID = session_id();
	}

	/**
	 * Returns Quote Item Query
	 * @return QuotdetQuery
	 */
	public function query() {
		return QuotdetQuery::create();
	}

	/**
	 * Returns Query Filtered By Quote Number and Line Number
	 * @param  string $qnbr    Quote Number
	 * @param  int    $linenbr Quote Line Number
	 * @return QuotdetQuery
	 */
	public function querySessionidQuote($qnbr) {
		return $this->query()->filterBySessionidQuote($this->sessionID, $qnbr);
	}

	/**
	 * Returns Editable Quote Item
	 * @param  string $qnbr    Quote Number
	 * @param  int    $linenbr Quote Line Number
	 * @return Quotdet
	 */
	public function item($qnbr, int $linenbr) {
		return $this->querySessionidQuote($qnbr)->filterByLinenbr($linenbr)->findOne();
	}

	/**
	 * Return if Editable Quote item Exists
	 * @param  string $qnbr    Quote Number
	 * @param  int    $linenbr Quote Line Number
	 * @return bool
	 */
	public function exists($qnbr, int $linenbr) {
		return boolval($this->querySessionidQuote($qnbr)->filterByLinenbr($linenbr)->count());
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
			case 'update-item-quick':
				$this->updateItemQuick($input);
				break;
		}
	}

	public function updateItemQuick(WireInput $input) {
		$values = $input->values();
		
		$item = $this->item($values->text('qnbr'), $values->int('linenbr'));
		$item->setQuotqty($values->float('qty'));
		$item->setQuotprice($values->text('price'));
		$item->setOrdrprice($values->text('price'));
		$item->setRshipdate($values->text('date_requested'));

		if ($values->offsetExists('orderquote')) {
			$qty = ($values->offsetExists('checkorder')) ? $values->float('qty') : 0;
			$item->setOrdrqty($qty);
		}  else {
			$item->setOrdrqty($values->float('qty'));
		}

		$saved = $item->save();
		$this->requestEditItem($item->quotenumber, $item->linenbr);
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
	 * Request Item Edit
	 * @param  string $qnbr    Quote Number
	 * @param  int    $linenbr Quote Line Number
	 * @return void
	 */
	public function requestEditItem($qnbr, int $linenbr) {
		$header = new Header();
		$custID = $header->getCustid($qnbr);
		$data = ['UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "LINENO=$linenbr", "CUSTID=$custID"];
		$this->requestDplus($data);
	}

}
