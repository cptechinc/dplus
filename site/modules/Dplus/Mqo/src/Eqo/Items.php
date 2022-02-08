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
			case 'add-item':
				$this->addItem($input);
				break;
			case 'delete-item':
				$this->deleteItem($input);
				break;
		}
	}

	/**
	 * Update Qty, Price, Date Requested on Quote Item
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function updateItemQuick(WireInput $input) {
		$values = $input->values();
		$item = $this->item($values->text('qnbr'), $values->int('linenbr'));
		$item->setQuotprice($values->text('price'));
		$item->setOrdrprice($values->text('price'));
		$item->setRshipdate($values->text('date_requested'));

		if ($values->offsetExists('orderquote')) {
			$qty = ($values->bool('checkorder')) ? $values->float('qty') : 0;
			$item->setOrdrqty($qty);
		}  else {
			$item->setQuotqty($values->float('qty'));
			$item->setOrdrqty($values->float('qty'));
		}

		$saved = $item->save();
		$this->requestEditItem($item->quotenumber, $item->linenbr);
	}

	/**
	 * Add Item to Quote
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function addItem(WireInput $input) {
		$values = $input->values();
		$qnbr   = $values->text('qnbr');
		$itemID = $values->text('itemID');
		$qty    = $values->int('qty');
		$price  = $values->text('price');
		$this->requestAddItem($qnbr, $itemID, $qty, $price);
	}

	private function addItems(WireInput $input) {
		$values = $input->values();
		$qtys   = $values->array('qty');
		$itemID = $values->array('itemID');
		$items  = [];

		for ($i = 0; $i < sizeof($qtys); $i++) {
			if (!empty($qtys[$i])) {
				$items[$itemID] = $qty;
			}
		}
		$this->requestAddItems($values->text('qnbr'), $items);
	}

	/**
	 * Delete Item from Quote
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function deleteItem(WireInput $input) {
		$values = $input->values();
		$this->requestDeleteItem($values->text('qnbr'), $values->int('linenbr'));
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
	private function requestEditItem($qnbr, int $linenbr) {
		$header = new Header();
		$custID = $header->getCustid($qnbr);
		$data = ['UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "LINENO=$linenbr", "CUSTID=$custID"];
		$this->requestDplus($data);
	}

	/**
	 * Request Item Edit
	 * @param  string $qnbr    Quote Number
	 * @param  string $itemID  Item ID
	 * @param  float  $qty     Qty to Add
	 * @param string $price  Price to Set
	 * @return void
	 */
	private function requestAddItem($qnbr, $itemID, $qty, $price = '') {
		$sanitizer = $this->wire('sanitizer');
		$price = empty($price) ? '' : $sanitizer->float($price, ['precision' => 3]);
		$data = ['UPDATEQUOTEDETAIL', "QUOTENO=$qnbr", "ITEMID=$itemID", "QTY=$qty", "PRICE=$price"];
		$this->requestDplus($data);
	}

	/**
	 * Request Item Delete
	 * @param  string $qnbr    Quote Number
	 * @param  array  $items   Items
	 * @return void
	 */
	private function requestAddItems($qnbr, array $items) {
		$data = ['QUOTEADDMULTIPLE', "QUOTENO=$qnbr"];
		foreach ($items as $itemID => $qty) {
			$itemID = str_pad($itemID, 30, ' ');
			$data[] = "ITEMID={$itemID}QTY=$qty";
		}
		$this->requestDplus($data);
	}

}
