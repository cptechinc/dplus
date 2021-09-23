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
	public function getOrCreate($itemID, $subitemID) {
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
		$subitemID = $subitemID == 'new' ? '' : $subitemID;
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
				$this->inputUpdate($input);
				break;
		}
	}

	/**
	 * Update Itm Dimension, Itm Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$itm = $this->getItm();
		$itm->init();
		$itemID = $itm->itemid($values->text('itemID'));

		if ($itm->exists($itemID) === false) {
			return false;
		}

		if ($itm->lockrecord($itemID) === false) {
			return false;
		}
		$this->inputUpdateSub($input);
	}

	private function inputUpdateSub(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$itemID    = $values->text('itemID');
		$subitemID = $values->text('subitemID');
		$sub = $this->getOrCreate($itemID, $subitemID);

		if ($sub->isNew() === false) {
			if ($this->lockrecord($sub) === false) {
				return false;
			}
		}
		$invalid = $this->setSubFields($sub, $input);
		$response = $this->saveAndRespond($sub, $invalid);
		$this->setResponse($response);
		exit;
	}

	private function setSubFields(ItemSubstitute $sub, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalid = [];

		$sub->setDate(date('Ymd'));
		$sub->setTime(date('His'));
		$invalid = $this->setSubFieldsValidated($sub, $input);
		return $invalid;
	}

	private function setSubFieldsValidated(ItemSubstitute $sub, WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalid = [];
		$itm = $this->getItm();

		if ($sub->isNew()) {
			if ($itm->exists($values->text('itemID')) === false) {
				$invalid['itemID'] = 'Item ID';
			}

			if ($itm->exists($values->text('itemID'))) {
				$sub->setItemid($values->text('itemID'));
			}
		}

		if ($itm->exists($values->text('subitemID')) === false) {
			$invalid['subitemID'] = 'Substitute Item ID';
		}

		if ($itm->exists($values->text('subitemID'))) {
			$sub->setSubitemid($itm->itemid($values->text('subitemID')));
		}

		$sameOrLike = $this->fieldAttribute('sameOrLike', 'default');
		if (array_key_exists($values->text('sameOrLike'), ItemSubstitute::OPTIONS_SAMEORLIKE)) {
			$sameOrLike = $values->text('sameOrLike');
		}
		$sub->setSameOrLike($sameOrLike);
		return $invalid;
	}


/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns ItmResponse based on the outcome of the database save
	 * @param  ItemSubstitute $sub        Record to record response of database save
	 * @param  array          $invalidfields Input fields that require attention
	 * @return Response
	 */
	private function saveAndRespond(ItemSubstitute $sub, array $invalidfields = []) {
		$is_new = $sub->isDeleted() ? false : $sub->isNew();
		$saved  = $sub->isDeleted() ? $sub->isDeleted() : $sub->save();

		$response = new Response();
		$response->setItemID($sub->itemid);

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($sub->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}

		$response->buildMessage(self::RESPONSE_TEMPLATE);

		if ($response->hasSuccess() && empty($invalidfields)) {
			$this->requestUpdate($sub->itemid, $sub->subitemid);
		}
		$response->setFields($invalidfields);
		return $response;
	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	protected function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'itm-sub', $response);
	}

	/**
	 * Get Session Response
	 * @return Response|null
	 */
	protected function getResponse() {
		$this->wire('session')->getFor('response', 'itm-sub');
	}

	/**
	 * Return if Field has Error
	 * NOTE: Uses $session->response_itm->fields to derive this
	 * @param  string $inputname Input name e.g. commissiongroup
	 * @return bool
	 */
	public function fieldHasError($inputname) {
		$response = $this->getResponse();
		return ($response) ? array_key_exists($inputname, $response->fields) : false;
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Request Update for ITM Substitute Record
	 * @param  string $itemID     Item ID
	 * @param  string $subitemID Substitute Item ID
	 * @return void
	 */
	private function requestUpdate($itemID, $subitemID) {
		$data = ['UPDATESUB', "ITEMID=$itemID", "SUBITEM=$subitemID"];
		$this->requestDplus($data);
	}

	/**
	 * Send Request
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
		$key = $this->getRecordlockerKey($sub);
		if ($this->recordlocker->isLocked($key) && $this->recordlocker->userHasLocked($key) === false) {
			return true;
		}
		if ($this->recordlocker->userhasLocked($key)) {
			return true;
		}
		return $this->recordlocker->lock($key);
	}

}
