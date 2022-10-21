<?php namespace Dplus\Xrefs;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Xref;
// Dplus Model
// use ItemMasterItemQuery, ItemMasterItem;
use ItemXrefUpcQuery, ItemXrefUpc;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Configs
use Dplus\Configs;
use Dplus\Filters;


/**
 * Upcx
 * Class for handling CRUD of ItemXrefUpc Records
 * 
 * @property string $updatePrimaryFor  Item ID that needs the old primary X-ref to be set to not Primary
 */
class Upcx extends AbstractXrefManager {
	const MODEL              = 'ItemXrefUpc';
	const MODEL_KEY          = ['upc', 'itemid'];
	const MODEL_TABLE        = 'upc_item_xref';
	const DESCRIPTION        = 'UPC Item X-Ref';
	const DESCRIPTION_RECORD = 'UPC X-ref {key} was {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'upcx';
	const FIELD_ATTRIBUTES = [
		'upc'         => ['type' => 'text', 'maxlength' => 20],
		'itemid'      => ['type' => 'text'],
		'qty'         => ['type' => 'number', 'default' => 1, 'min' => 1, 'max' => 99999999],
		'primary'     => ['type' => 'text', 'default' => 'N', 'primary' => 'Y', 'options' => ['Y' => 'Yes', 'N' => 'No']],
		'mastercase'  => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
		'needsleabel' => ['type' => 'text', 'default' => 'N', 'options' => ['Y' => 'Yes', 'N' => 'No']],
	];
	const FILTERABLE_FIELDS = ['upc', 'itemid'];
	const RESPONSE_TEMPLATE  = 'X-ref {code} {not} {crud}';
	/** DPlus Permission Xref */
	const PERMISSION = '';

	protected static $instance;

	protected $updatePrimaryFor = '';

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return Array ready for JSON
	 * @param  Xref  $xref Xref
	 * @return array
	 */
	public function xrefJson(Xref $xref) {
		$json = [];
		foreach (self::FIELD_ATTRIBUTES as $name => $data) {
			$json[$name] = $xref->$name;
		}
		return $json;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered By UPC
	 * @param  string $upc
	 * @return ItemXrefUpcQuery
	 */
	public function queryUpc($upc) {
		$q = $this->query();
		$q->filterByUpc($upc);
		return $q;
	}

	/**
	 * Return Query Filtered By UPC, Itemid
	 * @param  string $upc
	 * @param  string $itemid
	 * @return ItemXrefUpcQuery
	 */
	public function queryUpcItemid($upc, $itemID) {
		$q = $this->queryUpc($upc);
		$q->filterByItemid($itemID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if X-Ref exists
	 * @param  string $upc     UPC Code
	 * @param  string $itemID  Item ID
	 * @return bool
	 */
	public function exists($upc, $itemID) {
		$q = $this->queryUpcItemid($upc, $itemID);
		return boolval($q->count());
	}

	/**
	 * Return if X-Ref exists by key
	 * @param  string $key
	 * @return ItemXrefUpc
	 */
	public function existsByKey($key) {
		$keys = explode($this->recordlocker::glue(), $key);
		$upc = $keys[0];
		$itemID = $keys[1];
		return $this->exists($upc, $itemID);
	}

	/**
	 * Return ItemXrefUpc
	 * @param  string $upc     UPC Code
	 * @param  string $itemID  Item ID
	 * @return ItemXrefUpc
	 */
	public function xref($upc, $itemID) {
		$q = $this->queryUpcItemid($upc, $itemID);
		return $q->findOne();
	}

	/**
	 * Return ItemXrefUpc from Record Locker Key
	 * @param  string $upc     UPC Code
	 * @param  string $itemID  Item ID
	 * @return ItemXrefUpc
	 */
	public function xrefByKey($key) {
		$keys = explode($this->recordlocker::glue(), $key);
		$upc = $keys[0];
		$itemID = $keys[1];
		return $this->xref($upc, $itemID);
	}

	/**
	 * Return Primary UPC X-ref for Item ID
	 * @param  string  $itemID      Item ID
	 * @param  string  $excludeUpc  Exclude UPC Code
	 * @return ItemXrefUpc
	 */
	public function primaryUpcByItemid($itemID, $excludeUpc = '') {
		$q = $this->query();
		$q->select('upcxcode');
		if ($excludeUpc) {
			$q->filterByUpc($excludeUpc, Criteria::NOT_IN);
		}
		
		$q->filterByInititemnbr($itemID);
		$q->filterByPrimary(ItemXrefUpc::PRIMARY_TRUE);
		return $q->findOne();
	}

	/**
	 * Return if Primary UPC X-ref Exists for Item ID
	 * @param  string  $itemID      Item ID
	 * @param  string  $excludeUpc  Exclude UPC Code
	 * @return bool
	 */
	public function itemidHasPrimaryUpc($itemID, $excludeUpc = '') {
		$q = $this->query();
		if ($excludeUpc) {
			$q->filterByUpc($excludeUpc, Criteria::NOT_IN);
		}
		$q->filterByInititemnbr($itemID);
		$q->filterByPrimary(ItemXrefUpc::PRIMARY_TRUE);
		return boolval($q->count());
	}

	/**
	 * Return new ItemXrefUpc with Defaults set
	 * @return ItemXrefUpc
	 */
	public function new($upc = '', $itemID = '') {
		$xref = new ItemXrefUpc();
		
		$xref->setQty($this->fieldAttribute('qty', 'default'));
		$xref->setPrimary($this->fieldAttribute('primary', 'default'));
		$xref->setMastercase($this->fieldAttribute('mastercase', 'default'));
		$xref->setNeedslabel($this->fieldAttribute('needslabel', 'default'));
		$xref->setDummy('P');
		if ($upc != 'new' && $upc != '') {
			$upc = $this->sanitizer->string($upc, ['maxlength' => $this->fieldAttributes('upc', 'maxlength')]);
			$xref->setUpc($upc);
		}
		if ($itemID != 'new' && $itemID  != '') {
			$upc = $this->sanitizer->string($itemID);
			$xref->setUpc($upc);
		}
		return $xref;
	}

	/**
	 * Return ItemXrefUpc
	 * @param  string $upc     UPC Code
	 * @param  string $itemID Item ID
	 * @return ItemXrefUpc
	 */
	public function getOrCreateXref($upc, $itemID) {
		if ($this->exists($upc, $itemID)) {
			return $this->xref($upc, $itemID);
		}
		$itemID = $itemID == 'new' ? '' : $itemID;
		$xref = $this->new($upc, $itemID);
		return $xref;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Process Input Data, Update Database
	 * @param  WireInput $input Input Data
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'delete':
				$this->inputDelete($input);
				break;
			case 'update':
			case 'edit':
				$this->inputUpdate($input);
				break;
				
		}
	}

	/**
	 * Update Xref from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$upc = $values->string('upc');
		$itemID = $values->string('itemID');
		$invalidfields = [];

		$xref = $this->getOrCreateXref($upc, $itemID);

		if ($xref->isNew() === false && $this->lockrecord($xref) === false) {
			$message = self::DESCRIPTION_RECORD . " ($upc-$itemID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($xref);
			$this->setResponse(Response::responseError("$upc-$itemID", $message));
			return false;
		}

		$invalidfields = $this->_inputUpdate($input, $xref);
		$response      = $this->saveAndRespond($xref, $invalidfields);

		if ($response->hasSuccess() && $this->updatePrimaryFor != '') {
			$this->updatePrimaryXref($xref);
		}
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update Xref from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function _inputUpdate(WireInput $input, Xref $xref) {
		parent::_inputUpdate($input, $xref);

		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$invalidfields = [];
		$invalidfields['key']  = $this->_inputUpdateKey($values, $xref);
		$invalidfields['xref'] = $this->_inputUpdateXref($values, $xref);

		$invalid = array_merge_recursive($invalidfields);
		return $invalid;
	}

	/**
	 * Update Key Fields
	 * @param WireInputData    $values
	 * @param ItemXrefUpc $xref
	 * @return array
	 */
	private function _inputUpdateKey(WireInputData $values, ItemXrefUpc $xref) {
		$itm = $this->wire('modules')->get('Itm');
		$itemExists = $itm->exists($values->string('itemID'));
		$invalidfields = [];

		if ($itemExists) {
			$xref->setItemid($values->string('itemID'));
		}

		if ($itemExists === false) {
			$invalidfields['itemID'] = 'Item ID';
		}

		if ($xref->isNew() === false) {
			return $invalidfields;
		}
	}

	/**
	 * Update Qty, Primary, Mastercase, Need Label Fields
	 * @param WireInputData    $values
	 * @param ItemXrefUpc $xref
	 * @return array
	 */
	private function _inputUpdateXref(WireInputData $values, ItemXrefUpc $xref) {
		$xref->setQty($values->int('qty', ['max' => $this->fieldAttribute('qty', 'max'), 'blankValue' =>  $this->fieldAttribute('qty', 'default'), 'min' => $this->fieldAttribute('qty', 'min')]));
		$xref->setPrimary($values->yn('primary'));
		$xref->setMastercase($values->yn('mastercase'));
		$xref->setNaedslabel($values->yn('needslabel'));

		if ($xref->isPrimary()) {
			$this->updatePrimaryFor = $xref->itemid;
		}
		return [];
	}

	/**
	 * Delete Xref
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$upc    = $values->string('upc');
		$itemID = $values->string('itemID');

		if ($this->exists($upc, $itemID) === false) {
			return true;
		}
		$xref = $this->xref($upc, $itemID);
		
		if ($this->lockrecord($xref) === false) {
			$message = self::DESCRIPTION_RECORD . " ($upc-$itemID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($xref));
			$this->setResponse(Response::responseError("$upc-$itemID", $message));
			return false;
		}

		$xref->delete();
		$response = $this->saveAndRespond($xref);
		$$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update UPC Primary X-ref for Item ID
	 * NOTE: sets old primary to not be primary
	 * @param  ItemXrefUpc $xref
	 * @return bool
	 */
	public function updatePrimaryXref(ItemXrefUpc $xref) {
		if (empty($this->updatePrimaryFor) || $xref->isPrimary() === false) {
			$this->updatePrimaryFor = '';
			return true;
		}
		if ($this->updatePrimaryFor != $xref->itemid) {
			$this->updatePrimaryFor = '';
			return true;
		}
		if ($this->itemidHasPrimaryUpc($xref->itemid, $xref->upc) === false) {
			$this->updatePrimaryFor = '';
			return true;
		}
		$primaryXref = $this->primaryUpcByItemid($xref->itemid, $xref->upc);
		$primaryXref->setPrimary(ItemXrefUpc::PRIMARY_FALSE);
		$response = $this->saveAndRespond($primaryXref);
		return $response->hasSuccess();
	}

/* =============================================================
	Dplus Requests
============================================================= */
	protected function _generateRequestData(Xref $xref) {
		return ['UPDATEUPCX', "UPCCODE=$xref->upc", "OURITEM=$xref->itemid"];
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Options for Unit of Measure
	 * @return ObjectCollection
	 */
	public function getUomOptions() {
		$filter = new Filters\Min\UnitofMeasure();
		return $filter->query->find();
	}
}
