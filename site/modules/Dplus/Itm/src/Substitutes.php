<?php namespace Dplus\Min\Inmain\Itm;
// Dplus Model
use ItemSubstituteQuery, ItemSubstitute;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;

class Substitutes extends WireData {
	const MODEL              = 'ItemSubstitute';
	const MODEL_KEY          = 'itemid,subitemid';
	const DESCRIPTION        = 'Item Substitute';
	const DESCRIPTION_RECORD = 'Item Substitute';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Substitute {subitemid} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm-sub';

	public function __construct() {
		$this->sessionID = session_id();
		$this->fieldAttributes = [];
	}

	/**
	 * Return Options for the Same OR Like field
	 * @return array [key =>  value]
	 */
	public function getSameOrLikeOptions() {
		return ItemSubstitute::OPTIONS_SAMEORLIKE;
	}

/* =============================================================
	Field Attributes, Functions
============================================================= */
	const FIELD_ATTRIBUTES = [
		'sameOrLike' => ['default' => 'L'],
	];

	/**
	 * Initialize field attributes
	 * @return void
	 */
	public function initFieldAttributes() {
		$attributes = self::FIELD_ATTRIBUTES;
		$this->fieldAttributes = $attributes;
	}

	/**
	 * Return Field Attribute value
	 * @param  string $field Field Name
	 * @param  string $attr  Attribute Name
	 * @return mixed|bool
	 */
	public function fieldAttribute($field = '', $attr = '') {
		if (empty($this->fieldAttributes)) {
			$this->initFieldAttributes();
		}
		if (empty($field) || empty($attr)) {
			return false;
		}
		if (array_key_exists($field, $this->fieldAttributes) === false) {
			return false;
		}
		if (array_key_exists($attr, $this->fieldAttributes[$field]) === false) {
			return false;
		}
		return $this->fieldAttributes[$field][$attr];
	}


/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Get ItemSubstitute Record for Item ID
	 * @param  string $itemID    Item ID
	 * @param  string $subitemID Substitute Item ID
	 * @return ItemSubstitute
	 */
	public function getOrCreateSubstitute($itemID, $subitemID) {
		$q = $this->querySubstitute($itemID, $subitemID);
		if ($q->count()) {
			return $q->findOne();
		}
		return $this->newSubstitute($itemID);
	}

	/**
	 * Return if Item has Item Substitute Record
	 * @param  string $itemID    Item ID
	 * @param  string $subitemID Substitute Item ID
	 * @return bool
	 */
	public function exists($itemID, $subitemID) {
		$q = $this->querySubstitute($itemID, $subitemID);
		return boolval($q->count());
	}

	/**
	 * Return new ItemSubstitute
	 * @param  string $itemID    Item ID
	 * @param  string $subitemID Substitute Item ID
	 * @return ItemSubstitute
	 */
	public function newSubtitute($itemID, $subitemID) {
		$itm = $this->getItm();
		$sub = new ItemSubstitute();
		$sub->setItemid($itm->itemid($itemID));
		$sub->setSubtemid($itm->exists($subitemID) ? $itm->itemid($subitemID) : '');
		$sub->setSameOrLike($this->fieldAttribute('sameOrLike', 'default'));
		return $sub;
	}

	/**
	 * Return Query
	 * @return ItemSubstituteQuery
	 */
	public function query() {
		return ItemSubstituteQuery::create();
	}

	/**
	 * Return Query filtered by Item ID and Substitute Item ID
	 * @param  string $itemID     Item ID
	 * @param  string $subitemID  Substitute Item ID
	 * @return ItemSubstituteQuery
	 */
	public function querySubstitute($itemID, $subitemID) {
		$itm = $this->getItm();
		$q = $this->query();
		$q->filterByItemid($itm->itemid($itemID));
		$q->filterBySubitemid($itm->itemid($subitemID));
		return $q;
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
	// /**
	//  * Process Input Data and update ITM Dimensions
	//  * @param  WireInput $input Input Data
	//  * @return void
	//  */
	// public function processInput(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	// 	$values = $input->$rm;
	//
	// 	switch ($values->text('action')) {
	// 		case 'update':
	// 			$this->updateInput($input);
	// 			break;
	// 	}
	// }

	// /**
	//  * Update Itm Dimension, Itm Data
	//  * @param  WireInput $input Input Data
	//  * @return void
	//  */
	// private function updateInput(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	// 	$values = $input->$rm;
	//
	// 	$itm = $this->wire('modules')->get('Itm');
	// 	$itemID = $values->text('itemID');
	//
	// 	if ($itm->exists($itemID) === false) {
	// 		return false;
	// 	}
	// 	if ($itm->lockrecord($itemID) === false) {
	// 		return false;
	// 	}
	// 	// $this->updateInputItm($input);
	// 	$this->updateInputDimension($input);
	// }
	//
	// /**
	//  * Validate Item ID, Validate Item is locked for editing
	//  * @param  string $itemID Item ID
	//  * @return void
	//  */
	// private function validateAndLockItemid($itemID) {
	// 	$itm = $this->wire('modules')->get('Itm');
	// 	if ($itm->exists($itemID) === false) {
	// 		return false;
	// 	}
	// 	if ($itm->lockrecord($itemID) === false) {
	// 		return false;
	// 	}
	// 	return true;
	// }

	// /**
	//  * Update Itm
	//  * @param  WireInput $input Input Data
	//  * @return bool
	//  */
	// private function updateInputItm(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	// 	$values = $input->$rm;
	//
	// 	if ($this->validateAndLockItemid($values->text('itemID')) === false) {
	// 		return false;
	// 	}
	// 	$itm = $this->wire('modules')->get('Itm');
	// 	$item = $itm->item($values->text('itemID'));
	// 	$item->setQty_pack_inner($values->float('innerpack'));
	// 	$item->setQty_pack_outer($values->float('outerpack'));
	// 	$item->setQty_tare($values->float('qtytare'));
	// 	$item->setQtypercase($values->float('qtypercase'));
	// 	$item->setLiters($values->float('liters'));
	// 	$item->setWeight($values->float('weight'));
	// 	$item->setCubes($values->float('cubes'));
	// 	$response = $itm->save_and_respond($item);
	// 	$this->wire('session')->setFor('response', 'itm', $response);
	// 	return $response->has_success();
	// }

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns ItmResponse based on the outcome of the database save
	 * @param  ItemSubstitute $record        Record to record response of database save
	 * @param  array          $invalidfields Input fields that require attention
	 * @return Response
	 */
	public function saveAndRespond(ItemSubstitute $record, array $invalidfields = []) {
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
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return ITM CRUD
	 * @return Itm
	 */
	public function getItm() {
		return $this->wire('modules')->get('Itm');
	}

	/**
	 * Return Key for CXM Item
	 * @param  ItemSubstitute $item CXM Item
	 * @return string
	 */
	public function getRecordlockerKey(ItemSubstitute $item) {
		return implode(FunctionLocker::glue(), [$item->itemid, $item->subitemid]);
	}

	/**
	 * Lock the Substitute Record
	 * @param  ItemSubstitute $sub
	 * @return bool
	 */
	public function lockrecord(ItemSubstitute $sub) {
		if ($sub->isNew()) {
			return false;
		}
		if ($this->recordlocker->userhasLocked($this->getRecordlockerKey($sub))) {
			return true;
		}
		return $this->recordlocker->lock($this->getRecordlockerKey($sub));
	}
}
