<?php namespace Dplus\Mpm\Pmmain\Bmm;
// Dplus Models
use BomComponentQuery, BomComponent;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus RecordLocker
use Dplus\RecordLocker\Locker;
// Dplus Bmm CRUD
use Dplus\Mpm\Pmmain\Bmm;

class Components extends WireData {
	const MODEL              = 'BomComponent';
	const MODEL_KEY          = 'produces, itemid';
	const DESCRIPTION        = 'BoM Component';
	const DESCRIPTION_RECORD = 'BoM Component';
	const RESPONSE_TEMPLATE  = 'BoM {bomID} Component {component} {not} {crud}';

	public function __construct() {
		$this->sessionID = session_id();
	}

/* =============================================================
	Field Attributes, Functions
============================================================= */
	const FIELD_ATTRIBUTES = [
		'qty' => ['default' => 0, 'min' => 1],
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
	Queries
============================================================= */
	/**
	 * Return Query
	 * @return BomComponentQuery
	 */
	public function query() {
		return BomComponentQuery::create();
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponentQuery
	 */
	public function queryComponent($bomID, $componentID) {
		$q = $this->query();
		$q->filterByProduces($bomID);
		$q->filterByItemid($componentID);
		return $q;
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @return BomComponentQuery
	 */
	public function queryBomId($bomID) {
		$q = $this->query();
		$q->filterByProduces($bomID);
		return $q;
	}

/* =============================================================
	CRUD Reads
============================================================= */
	/**
	 * Return If BomComponent Exists
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return bool
	 */
	public function component($bomID, $componentID) {
		$q = $this->queryComponent($bomID, $componentID);
		return $q->findOne();
	}

	/**
	 * Return Query Filtered By Itemid, Level
	 * @param  string $bomID       Bom Item ID
	 * @return array
	 */
	public function getComponentIds($bomID) {
		$q = $this->queryBomId($bomID);
		$q->select(BomComponent::aliasproperty('itemid'));
		return $q->find()->toArray();
	}

	/**
	 * Return BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return bool
	 */
	public function exists($bomID, $componentID) {
		$q = $this->queryComponent($bomID, $componentID);
		return boolval($q->count());
	}

	/**
	 * Return if Components Exist for BoM
	 * @param  string $bomID       Bom Item ID
	 * @return bool
	 */
	public function hasComponents($bomID) {
		$q = $this->queryBomId($bomID);
		return boolval($q->count());
	}

/* =============================================================
	CRUD Create
============================================================= */
	/**
	 * Return BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponent
	 */
	public function new($bomID, $componentID) {
		$componentID = $componentID != 'new' ? $componentID : '';
		$c = new BomComponent();
		$c->setProduces($bomID);
		$c->setItemid($componentID);
		$c->setScrap('N');
		$c->setQty(0);
		$c->setDummy('P');
		return $c;
	}

	/**
	 * Return New or Existing BomComponent
	 * @param  string $bomID       Bom Item ID
	 * @param  string $componentID Component Item ID
	 * @return BomComponent
	 */
	public function getOrCreate($bomID, $componentID) {
		if ($this->exists($bomID, $componentID)) {
			return $this->component($bomID, $componentID);
		}
		return $this->new($bomID, $componentID);
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Input Data, call function
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update-component':
				$this->inputUpdate($input);
				break;
			case 'delete-component':
				$this->inputDelete($input);
				break;
		}
	}

	/**
	 * Delete Bmm Component
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputDelete(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		$bmmHeader = new Header();
		$bmmHeader->lockrecord($values->text('bomID'));

		if ($bmmHeader->recordlocker->isLocked($values->text('bomID')) && $bmmHeader->recordlocker->userHasLocked($values->text('bomID')) === false) {
			$msg = 'BoM ' . $values->text('bomID') . ' is being locked by ' . $bmmHeader->recordlocker->getLockingUser();
			$response = Response::responseError($values->text('bomID'), $msg);
			return false;
		}

		if ($this->exists($values->text('bomID'), $values->text('component') === false)) {
			return true;
		}
		$component = $this->component($values->text('bomID'), $values->text('component'));
		$component->delete();
		$response = $this->saveAndRespond($component);
		Bmm::setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update Bmm
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function inputUpdate(WireInput $input) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;

		$bmmHeader = new Header();

		if ($bmmHeader->exists($values->text('bomID')) === false) {
			$bmmHeader->createHeader($values->text('bomID'));
			sleep(2);
		}

		$bmmHeader->lockrecord($values->text('bomID'));

		if ($bmmHeader->recordlocker->isLocked($values->text('bomID')) && $bmmHeader->recordlocker->userHasLocked($values->text('bomID')) === false) {
			$msg = 'BoM ' . $values->text('bomID') . ' is being locked by ' . $bmmHeader->recordlocker->getLockingUser();
			$response = Response::responseError($values->text('bomID'), $msg);
			return false;
		}

		$component = $this->getOrCreate($values->text('bomID'), $values->text('component'));
		$errors = $this->setComponentFields($input, $component);
		$response = $this->saveAndRespond($component, $errors);
		Bmm::setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Set field values for Component
	 * @param WireInput    $input      Input Data
	 * @param BomComponent $component  BoM Component
	 */
	private function setComponentFields(WireInput $input, BomComponent $component) {
		$errors = [];
		$component->setDate(date('Ymd'));
		$component->setTime(date('His'));

		$errors = $this->setComponentFieldsValidated($input, $component);
		return $errors;
	}

	/**
	 * Set Validated Field Values
	 * @param WireInput    $input      Input Data
	 * @param BomComponent $component  BoM Component
	 */
	private function setComponentFieldsValidated(WireInput $input, BomComponent $component) {
		$rm     = strtolower($input->requestMethod());
		$values = $input->$rm;
		$errors = [];

		if ($values->int('qty') >= 1) {
			$component->setQty($values->int('qty'));
		}

		if ($values->int('qty') < 1) {
			$errors['qty'] = "Usage Qty must be more than 0";
		}

		return $errors;
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns ItmResponse based on the outcome of the database save
	 * @param  BomComponent $component        Record to record response of database save
	 * @param  array        $errors           Input fields that require attention
	 * @return Response
	 */
	private function saveAndRespond(BomComponent $component, array $errors = []) {
		$is_new = $component->isDeleted() ? false : $component->isNew();
		$saved  = $component->isDeleted() ? $component->isDeleted() : $component->save();

		$locker = Bmm::getRecordLocker();
		$response = new Response();
		$response->bomID       = $component->produces;
		$response->componentID = $component->itemid;
		$response->setKey(implode(Locker::GLUE, [$component->produces,$component->itemid]));

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($component->isDeleted()) {
			$response->setAction(Response::CRUD_DELETE);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}
		$response->buildMessage(self::RESPONSE_TEMPLATE);
		$response->setFields($errors);

		if ($response->hasSuccess() && empty($errors)) {
			$this->requestUpdateComponent($component->produces, $component->itemid);
		}
		return $response;
	}

/* =============================================================
	Dplus Cobol Request Functions
============================================================= */
	/**
	 * Send Request Data to Dplus
	 * @param  array  $data
	 * @return void
	 */
	private function requestDplus(array $data) {
		$config  = $this->wire('config');
		$dplusdb = $this->wire('modules')->get('DplusDatabase')->db_name;
		$data = array_merge(["DBNAME=$dplusdb"], $data);
		$requestor = $this->wire('modules')->get('DplusRequest');
		$requestor->write_dplusfile($data, $this->sessionID);
		$requestor->cgi_request($config->cgis['database'], $this->sessionID);
	}

	/**
	 * Writes File for Dplus to update the ITM file for this ITEM
	 * @param  string $itemID Item ID
	 * @return void
	 */
	public function requestUpdateComponent($bomID, $componentID) {
		$data = array('UPDATEBMM', "ITEMID=$bomID", "COMPITEM=$componentID");
		$this->requestDplus($data);
	}
}
