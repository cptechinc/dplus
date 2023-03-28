<?php namespace Dplus\Min\Inmain\Itm;

use ItmDimensionQuery, ItmDimension;

use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;
use Dplus\Min\Inmain\Itm\Response;

class Dimensions extends WireData {
	const MODEL              = 'ItmDimension';
	const MODEL_KEY          = 'itemid';
	const DESCRIPTION        = 'Item Dimensions';
	const DESCRIPTION_RECORD = 'Item Dimensions';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Dimensions {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm';

	public function __construct() {
		$this->sessionID = session_id();
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Get ItmDimension Record for Item ID
	 * @param  string $itemID Item ID
	 * @return ItmDimension
	 */
	public function getOrCreateDimension($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);

		if ($q->count()) {
			return $q->findOne();
		}
		return $this->newDimension($itemID);
	}

	/**
	 * Return if Item has Itm Dimension Record
	 * @param  string $itemID Item ID
	 * @return bool
	 */
	public function exists($itemID) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return new ItmDimension
	 * @param  string $itemID Item ID
	 * @return ItmDimension
	 */
	public function newDimension($itemID) {
		$dim = new ItmDimension();
		$dim->setItemid($itemID);
		return $dim;
	}

	/**
	 * Return Query
	 * @return ItmDimensionQuery
	 */
	public function query() {
		return ItmDimensionQuery::create();
	}

	/**
	 * Set up Functions / Properties for pw_templated pages
	 * @return void
	 */
	public function init() {
		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

/* =============================================================
	Input Functions
============================================================= */
	/**
	 * Process Input Data and update ITM Dimensions
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update':
				$this->updateInput($input);
				break;
		}
	}

	/**
	 * Update Itm Dimension, Itm Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function updateInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$itm = $this->wire('modules')->get('Itm');
		$itemID = $values->string('itemID');

		if ($itm->exists($itemID) === false) {
			return false;
		}
		if ($itm->lockrecord($itemID) === false) {
			return false;
		}
		// $this->updateInputItm($input);
		$this->updateInputDimension($input);
	}

	/**
	 * Validate Item ID, Validate Item is locked for editing
	 * @param  string $itemID Item ID
	 * @return void
	 */
	private function validateAndLockItemid($itemID) {
		$itm = $this->wire('modules')->get('Itm');
		if ($itm->exists($itemID) === false) {
			return false;
		}
		if ($itm->lockrecord($itemID) === false) {
			return false;
		}
		return true;
	}

	/**
	 * Update Itm
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function updateInputItm(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($this->validateAndLockItemid($values->string('itemID')) === false) {
			return false;
		}
		$itm = $this->wire('modules')->get('Itm');
		$item = $itm->item($values->string('itemID'));
		$item->setQty_pack_inner($values->float('innerpack'));
		$item->setQty_pack_outer($values->float('outerpack'));
		$item->setQty_tare($values->float('qtytare'));
		$item->setQtypercase($values->float('qtypercase'));
		$item->setLiters($values->float('liters'));
		$item->setWeight($values->float('weight'));
		$item->setCubes($values->float('cubes'));
		$response = $itm->save_and_respond($item);
		$this->wire('session')->setFor('response', 'itm', $response);
		return $response->has_success();
	}

	/**
	 * Update Itm Dimension
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function updateInputDimension(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		if ($this->validateAndLockItemid($values->string('itemID')) === false) {
			return false;
		}

		$dim = $this->getOrCreateDimension($values->string('itemID'));
		$dim->setLength($values->float('length'));
		$dim->setWidth($values->float('width'));
		$dim->setThickness($values->float('thickness'));
		$dim->setSqft($values->float('sqft'));

		$response = $this->saveAndRespond($dim);
		$this->wire('session')->setFor('response', 'itm-dim', $response);
		return $response->hasSuccess();
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns ItmResponse based on the outcome of the database save
	 * @param  ItmDimension $record        Record to record response of database save
	 * @param  array          $invalidfields Input fields that require attention
	 * @return Response
	 */
	public function saveAndRespond(ItmDimension $record, array $invalidfields = []) {
		$is_new = $record->isDeleted() ? false : $record->isNew();
		$saved  = $record->isDeleted() ? $record->isDeleted() : $record->save();

		$response = new Response();
		$response->setItemID($record->itemid);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($record->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->buildMessage(self::RESPONSE_TEMPLATE);

		if ($response->hasSuccess() && empty($invalidfields)) {
			$this->requestUpdate($record->itemid);
		}
		$response->setFields($invalidfields);
		return $response;
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Request Update for ITM Dimension Records
	 * @param  string $itemID Item ID
	 * @return void
	 */
	private function requestUpdate($itemID) {
		$data = ['UPDATEITMDIMEN', "ITEMID=$itemID"];
		$this->requestDplus($data);
	}

	/**
	 * [requestDplus description]
	 * @param  array  $data Data
	 * @return void
	 */
	private function requestDplus(array $data) {
		$config = $this->wire('config');
		$dplusdb = DbDplus::instance()->dbconfig->dbName;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}
}
