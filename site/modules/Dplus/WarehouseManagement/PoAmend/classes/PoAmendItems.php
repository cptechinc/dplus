<?php namespace ProcessWire;

use Propel\Runtime\ActiveQuery\Criteria;
use Purl\Url;

use PurchaseOrderDetailQuery, PurchaseOrderDetail;
use PurchaseOrderDetailReceivingQuery, PurchaseOrderDetailReceiving;
use PurchaseOrderDetailReceiptQuery, PurchaseOrderDetailReceipt;
use WarehouseQuery, Warehouse;

use EditPoDetailQuery, EditPoDetail;

class PoAmendItems extends WireData {
	public function __construct() {
		$this->sessionID = session_id();
		$this->configs = $this->wire('modules')->get('PurchaseOrderEditConfigs');
	}

	/**
	 * Returns Query for Purchase Order Details Edit
	 * @param  string $ponbr Purchase Order Number
	 * @return EditPoDetailQuery
	 */
	public function query($ponbr) {
		$q = EditPoDetailQuery::create();
		$q->filterBySessionid($this->sessionID);
		$q->filterByPonbr($ponbr);
		return $q;
	}

	/**
	 * Return if PO items are available for editing
	 * @param  string $ponbr Purchase Order Number
	 * @return bool
	 */
	public function exist($ponbr) {
		$q = $this->query($ponbr);
		return boolval($q->count());
	}

	/**
	 * Return All Items
	 * @param  string $ponbr Purchase Order Number
	 * @return EditPoDetail[]
	 */
	public function all($ponbr) {
		return $this->query($ponbr)->find();
	}

	public function can_edit($ponbr, $linenbr = 1) {
		if ($this->is_closed($ponbr, $linenbr)) {
			return false;
		}

		if ($this->has_received($ponbr, $linenbr)) {
			return false;
		}

		if ($this->has_receipt($ponbr, $linenbr)) {
			return false;
		}
		return true;
	}

	/**
	 * Return if Detail Line is Closed
	 * @param  string $ponbr   Purchase Order Number
	 * @param  int    $linenbr Line Number
	 * @return bool
	 */
	public function is_closed($ponbr, $linenbr = 1) {
		$q = $this->query_podetail($ponbr, $linenbr);
		$q->select(PurchaseOrderDetail::get_aliasproperty('status'));
		return $q->findOne() == PurchaseOrderDetail::STATUS_CLOSED;
	}

	/**
	 * Return if Detail Line is Closed
	 * @param  string $ponbr   Purchase Order Number
	 * @param  int    $linenbr Line Number
	 * @return bool
	 */
	public function has_received($ponbr, $linenbr = 1) {
		return boolval($this->qty_received($ponbr, $linenbr));
	}

	/**
	 * Return if Detail Line is Closed
	 * @param  string $ponbr   Purchase Order Number
	 * @param  int    $linenbr Line Number
	 * @return bool
	 */
	public function has_receipt($ponbr, $linenbr = 1) {
		return boolval($this->qty_receipt($ponbr, $linenbr));
	}

/* =============================================================
	CRUD Processing Functions
============================================================= */
	/**
	 * Process Input Data and act on upon action
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function process_input(WireInput $input) {
		$this->init_configs();
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'add-item':
				$this->input_add_item($input);
				break;
			case 'update-item':
				$this->input_update_item($input);
				break;
		}
	}

	/**
	 * Add Item To Purchase Order
	 * @param WireInput $input Input data
	 * @return void
	 */
	public function input_add_item(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$q = $this->query($values->text('ponbr'));
		$count_before = $q->count();
		$this->request_add_item($values->text('ponbr'), $values->text('itemID'), $values->int('qty'), $values->float('cost'));
		$count_after = $q->count();

		if ($count_after > $count_before) {
			$response = MpoResponse::response_success($values->text('ponbr'), $values->text('itemID') . ' was added to PO');
		} else {
			$response = MpoResponse::response_error($values->text('ponbr'), $values->text('itemID') . ' was not added to PO');
		}
		$this->wire('session')->response_epo = $response;
	}

	/**
	 * Update / Edit Purchase Order Item
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function input_update_item(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$ponbr = $values->text('ponbr');
		$linenbr = $values->int('linenbr');
		$q = $this->query($ponbr);
		$item = $q->findOneByLinenbr($linenbr);
		$this->update_item($item, $input);

		if ($item->save()) {
			$this->request_update_item($ponbr, $linenbr);
			$response = MpoResponse::response_success($values->text('ponbr'), "Line #$linenbr was updated");
		} else {
			$response = MpoResponse::response_error($values->text('ponbr'), "Line #$linenbr was not updated");
		}
		$this->wire('session')->response_epo = $response;
	}

	/**
	 * Updates EditPoDetail Record
	 * @param  EditPoDetail $item  Purchase Order Item
	 * @param  WireInput    $input Input Data
	 * @return void
	 */
	protected function update_item(EditPoDetail $item, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$item->setItemid($values->text('itemID'));
		$item->setDescription($values->text('description'));
		$item->setVendoritemid($values->text('vendoritemID'));
		$item->setWhse($values->text('whse'));
		$item->setQty_ordered($values->text('qty_ordered'));
		$item->setCost($values->text('cost'));
		$item->setCost_total($values->text('cost_total'));
	}

/* =============================================================
	Dplus Request Functions
============================================================= */
	/**
	 * Send PO Add Item Request
	 * @param string $ponbr  Purchase Order Number
	 * @param string $itemID Item ID
	 * @param int    $qty    Qty
	 * @param float  $cost   Cost
	 */
	public function request_add_item($ponbr, $itemID, int $qty = 1, $cost = 0.0) {
		$data = array('ADDPURCHASEORDERLINE', "PONBR=$ponbr", "ITEMID=$itemID", "QTY=$qty");
		if ($cost) {
			$data[] = "COST=$cost";
		}
		$this->request_dplus($data);
	}

	/**
	 * Send PO Update Item
	 * @param  string $ponbr   Purchase Order Number
	 * @param  int    $linenbr Line Number
	 * @return void
	 */
	public function request_update_item($ponbr, int $linenbr = 0) {
		$data = array('SAVEPURCHASEORDERLINE', "PONBR=$ponbr", "LINE=$linenbr");
		$this->request_dplus($data);
	}

	protected function request_dplus(array $data) {
		$config = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusOnlineDatabase')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['default'], $this->sessionID);
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	/**
	 * Loads configs needed into $configs property
	 * @return void
	 */
	public function init_configs() {
		$this->configs->init_configs();
	}

	public function query_podetail($ponbr, $linenbr) {
		$q = PurchaseOrderDetailQuery::create();
		$q->filterByPonbr($ponbr);
		$q->filterByLinenbr($linenbr);
		return $q;
	}

	public function podetail($ponbr, $linenbr) {
		$q = $this->query_podetail($ponbr, $linenbr);
		return $q->findOne();
	}

	/**
	 * Return Warehouses
	 * @return Warehouse[]|ObjectCollection
	 */
	public function warehouses() {
		return WarehouseQuery::create()->find();
	}

	/**
	 * Return ITM item
	 * @param  string $itemID Item ID
	 * @return ItemMasterItem
	 */
	public function itmitem($itemID) {
		return ItemMasterItemQuery::create()->findOneByItemid($itemID);
	}

	/**
	 * Return Qty Received from the Database
	 * @return float
	 */
	public function qty_received($ponbr, $linenbr = 1) {
		$q = PurchaseOrderDetailReceivingQuery::create();
		$q->withColumn('SUM(potdqtyrec)', 'qty');
		$q->select('qty');
		$q->filterByPonbr($ponbr);
		$q->filterByLinenbr($linenbr);
		return floatval($q->findOne());
	}

	/**
	 * Return Qty Received from the Database
	 * @return float
	 */
	public function qty_receipt($ponbr, $linenbr = 1) {
		$q = PurchaseOrderDetailReceiptQuery::create();
		$q->withColumn('SUM(pordqtyrec)', 'qty');
		$q->select('qty');
		$q->filterByPonbr($ponbr);
		$q->filterByLinenbr($linenbr);
		return floatval($q->findOne());
	}
}
