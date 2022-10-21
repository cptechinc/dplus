<?php namespace Dplus\Xrefs;
// Dplus Model
// use ItemMasterItemQuery, ItemMasterItem;
use ItemXrefCustomerQuery, ItemXrefCustomer;
use ConfigSalesOrder as ConfigSo;
// Propel Classes
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Xref;
// use Propel\Runtime\Collection\ObjectCollection;
// ProcessWire
use ProcessWire\WireInput;
use ProcessWire\WireInputData;
// Dplus Configs
use Dplus\Configs;
use Dplus\Codes;
use Dplus\Filters;


/**
 * Cxm
 * Class for handling CRUD of ItemXrefCustomer Records
 */
class Cxm extends AbstractXrefManager {
	const MODEL              = 'ItemXrefCustomer';
	const MODEL_KEY          = ['custid', 'custitemid'];
	const MODEL_TABLE        = 'cust_item_xref';
	const DESCRIPTION        = 'Customer Item X-Ref';
	const DESCRIPTION_RECORD = 'CXM X-ref {key} was {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cxm';
	const FIELD_ATTRIBUTES = [
		'custitemid'     => ['type' => 'text', 'maxlength' => ItemXrefCustomer::LENGTH_CUSTITEMID],
		'description'    => ['type' => 'text', 'maxlength' => ItemXrefCustomer::LENGTH_DESCRIPTION],
		'description2'   => ['type' => 'text', 'maxlength' => ItemXrefCustomer::LENGTH_DESCRIPTION],
		'conversion'     => ['type' => 'number', 'precision' => 5, 'max' => 99999],
		'qty_percase'    => ['type' => 'number', 'precision' => 0, 'max' => 9999],
		'qty_purchase'   => ['type' => 'number', 'precision' => 0, 'max' => 999999],
		'price_retail'   => ['type' => 'number', 'precision' => 2, 'max'  => 9999999.99],
		'price_customer' => ['type' => 'number', 'precision' => 2, 'max'  => 9999999.99],
		'price_uom'      => ['type' => 'number', 'precision' => 3, 'max'  => 999999.999],
		'revision'       => ['type' => 'text', 'maxlength' => 10],
		'qty_pack_inner'    => ['type' => 'number', 'precision' => 0, 'max' => 9999],
		'qty_pack_outer'    => ['type' => 'number', 'precision' => 0, 'max' => 9999],
		'qty_pack_tare'     => ['type' => 'number', 'precision' => 0, 'max' => 9999],
		'rounding'          => ['type' => 'text', 'default' => 'N', 'options' => ['D' => 'down', 'U' => 'up', 'N' => 'normal']]
	];
	const FILTERABLE_FIELDS = ['custitemid', 'itemid', 'description'];
	const RESPONSE_TEMPLATE  = 'X-ref {code} {not} {crud}';
	/** DPlus Permission Xref */
	const PERMISSION = '';

	protected static $instance;

	public static function instance() {
		if (empty(static::$instance)) {
			static::$instance = new static();
		}
		return static::$instance;
	}

	/**
	 * Return if CXM should update PDM
	 * @return bool
	 */
	public function allowPdmUpdate() {
		/** @var ConfigSo */
		$configSo = Configs\So::config();
		return $configSo->update_pdm_from_cxm();
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
	 * Return Query Filtered By Cust ID, Cust Item ID
	 * @param  string $custID     Cust ID
	 * @param  string $custitemID Customer's Item ID'
	 * @return ItemXrefCustomerQuery
	 */
	public function queryUpc($custID, $custitemID) {
		$q = $this->query();
		$q->filterByCustid($custID);
		$q->filterByCustitemid($custitemID);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return if X-Ref exists
	 * @param  string $custID     Cust ID
	 * @param  string $custitemID Customer's Item ID'
	 * @return bool
	 */
	public function exists($custID, $custitemID) {
		$q = $this->queryCustidCustitemid($custID, $custitemID);
		return boolval($q->count());
	}

	/**
	 * Return ItemXrefCustomer
	 * @param  string $custID     Cust ID
	 * @param  string $custitemID Customer's Item ID'
	 * @return ItemXrefCustomer
	 */
	public function xref($custID, $custitemID) {
		$q = $this->queryCustidCustitemid($custID, $custitemID);
		return $q->findOne();
	}

	/**
	 * Return ItemXrefCustomer from Record Locker Key
	 * @param  string $custID     Cust ID
	 * @param  string $custitemID Cust's Item ID'
	 * @return ItemXrefCustomer
	 */
	public function xrefByRecordlockerKey($key) {
		$keys = explode($this->recordlocker::glue(), $key);
		$custID = $keys[0];
		$custitemID = $keys[1];
		return $this->xref($custID, $custitemID);
	}

	/**
	 * Return ItemXrefCustomer and set Defaults
	 * @return ItemXrefCustomer
	 */
	public function new() {
		$xref = ItemXrefCustomer::new();
		$xref->setConversion(1);
		return $xref;
	}

	/**
	 * Return ItemXrefCustomer
	 * @param  string $custID     Cust ID
	 * @param  string $custitemID Cust's Item ID'
	 * @return ItemXrefCustomer
	 */
	public function getOrCreateXref($custID, $custitemID) {
		if ($this->exists($custID, $custitemID)) {
			return $this->xref($custID, $custitemID);
		}
		$custitemID = $custitemID == 'new' ? '' : $custitemID;
		$xref = $this->new();
		$xref->setCustid($custID);
		$xref->setCustitemid($custitemID);
		return $xref;
	}

	/**
	 * Return if Short Item for Item ID exists
	 * @param  string  $itemID
	 * @return bool
	 */
	public function itemidHasShortItemid($itemID) {
		$q = $this->query();
		$q->filterByCustid(Configs\Sys::config()->custid);
		$q->filterByItemid($itemID);
		return boolval($q->count());
	}

	/**
	 * Return if Short Item for Item ID exists
	 * @param  string  $itemID
	 * @return bool
	 */
	public function shortitemidExists($custitemID) {
		return $this->exists(Configs\Sys::config()->custid, $custitemID);
	}

	/**
	 * Return the CXM Shortitem X-Ref Item ID
	 * @param  string $itemID Item ID / Our Item ID
	 * @return ItemXrefCustomer
	 */
	public function shortitemByItemid($itemID) {
		$q = $this->query();
		$q->filterByCustid(Configs\Sys::config()->custid);
		$q->filterByItemid($itemID);
		return $q->findOne();
	}

	/**
	 * Return CXM X-Ref By Short Item ID
	 * @param  string $shortitemID
	 * @return ItemXrefCustomer
	 */
	public function shortitem($shortitemID) {
		return $this->xref(Configs\Sys::config()->custid, $shortitemID);
	}

	/**
	 * Return New / Existing CXM item for ItemID with the ConfigSys cust ID
	 * @param  string $itemID Item ID
	 * @return ItemXrefCustomer
	 */
	public function getOrCreateXrefShortitem($itemID, $custitemID = '') {
		if ($this->shortitemExists($itemID)) {
			return $this->shortitemByItemid($itemID);
		}
		$custID = Configs\Sys::config()->custid;
		$custitemID = in_array($custitemID, ['new', ''])  ? '' : $custitemID;

		$xref = ItemXrefCustomer::new();
		$xref->setItemid($itemID);
		$xref->setCustid($custID);
		$$xref->setCustitemid($custitemID);
		return $xref;
	}

	/**
	 * Return Cust IDs of Existing CXM items
	 * @return array
	 */
	public function custids() {
		$q = $this->query();
		$q->select(ItemXrefCustomer::get_aliasproperty('custid'));
		$q->distinct();
		return $q->find()->toArray();
	}

	/**
	 * Return if Cust ID has an X-Ref
	 * @param  string $custID     Cust ID
	 * @return bool
	 */
	public function custidExists($custID) {
		$q = $this->query();
		$q->filterByCustid($custID);
		return boolval($q->count());
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
			case 'update-cxm-shortitem':
				$this->inputUpdateShortitem($input);
				break;
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
		$custID = $values->string('custID');
		if ($values->string('original_custitemID') == '') {
			$values->original_custitemID = $values->custitemID;
		}
		$custitemID = $values->string('original_custitemID') != $values->string('custitemID') ? $values->string('original_custitemID') : $values->string('custitemID');
		$invalidfields = [];

		$xref = $this->getOrCreateXref($custID, $custitemID);

		if ($xref->isNew() === false && $this->lockrecord($xref) === false) {
			$message = self::DESCRIPTION_RECORD . " ($custID-$custitemID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($xref);
			$this->setResponse(Response::responseError("$custID-$custitemID", $message));
			return false;
		}

		$invalidfields = $this->_inputUpdate($input, $xref);
		$response      = $this->saveAndRespond($xref, $invalidfields);
		if ($response->hasSuccess() && $values->text('updatepdm') == 'true') {
			$this->updatePdm($xref);
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
		$invalidfields['key']          = $this->_inputUpdateKey($values, $xref);
		$invalidfields['descriptions'] = $this->_inputUpdateDescriptions($values, $xref);
		$invalidfields['qtys']         = $this->_inputUpdateQtysConversion($values, $xref);
		$invalidfields['prices']       = $this->_inputUpdatePrices($values, $xref);
		$invalidfields['packqtys']     = $this->_inputUpdatePackQtys($values, $xref);
		$invalidfields['uom']          = $this->_inputUpdateUoM($values, $xref);
		$invalidfields['rounding']     = $this->_inputUpdateRounding($values, $xref);

		$invalid = array_merge_recursive($invalidfields);
		return $invalid;
	}

	/**
	 * Update Key Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdateKey(WireInputData $values, ItemXrefCustomer $xref) {
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
		$cmm = Codes\Mar\Cmm::instance();
		
		if ($cmm->exists($values->string('custID')) === false) {
			$invalidfields['custID'] = 'Customer ID';
			return $invalidfields;
		}
		$xref->setCustid($values->string('custID'));
		return $invalidfields;
	}

	/**
	 * Update Descriptions, Revision Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdateDescriptions(WireInputData $values, ItemXrefCustomer $xref) {
		$xref->setDescription($values->text('description', ['maxLength' => $this->fieldAttribute('description', 'maxlength')]));
		$xref->setDescription2($values->text('description2', ['maxLength' => $this->fieldAttribute('description', 'maxlength')]));
		$xref->setRevision($values->text('revision', ['maxLength' => $this->fieldAttribute('revision', 'maxlength')]));
		return [];
	}

	/**
	 * Update Conversion, Qty Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdateQtysConversion(WireInputData $values, ItemXrefCustomer $xref) {
		$xref->setConversion($values->float('conversion', ['precision' => $this->fieldAttribute('conversion', 'precision'), 'max' => $this->fieldAttribute('conversion', 'max')]));
		$xref->setQty_percase($values->int('qty_percase',   ['max' => $this->fieldAttribute('qty_percase', 'max')]));
		$xref->setQty_purchase($values->int('qty_purchase', ['max' => $this->fieldAttribute('qty_purchase', 'max')]));
		return [];
	}

	/**
	 * Update Prices Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdatePrices(WireInputData $values, ItemXrefCustomer $xref) {
		$xref->setPrice_retail($values->float('price_retail', ['precision' => $this->fieldAttribute('price_retail', 'precision'), 'max' => $this->fieldAttribute('price_retail', 'max')]));
		$xref->setPrice_customer($values->float('price_customer', ['precision' => $this->fieldAttribute('price_customer', 'precision'), 'max' => $this->fieldAttribute('price_customer', 'max')]));
		$xref->setPrice_customer($values->float('price_customer', ['precision' => $this->fieldAttribute('price_customer', 'precision'), 'max' => $this->fieldAttribute('price_customer', 'max')]));

		if ($xref->price_uom != '') {
			$xref->setPrice_uom($values->float('price_uom', ['precision' => $this->fieldAttribute('price_uom', 'precision')]));
		}
		return [];
	}

	/**
	 * Update Pack Qty Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdatePackQtys(WireInputData $values, ItemXrefCustomer $xref) {
		$xref->setQty_pack_inner($values->float('qty_pack_inner', ['precision' => $this->fieldAttribute('qty_pack_inner', 'precision'), 'max' => $this->fieldAttribute('qty_pack_inner', 'max')]));
		$xref->setQty_pack_outer($values->float('qty_pack_outer', ['precision' => $this->fieldAttribute('qty_pack_outer', 'precision'), 'max' => $this->fieldAttribute('qty_pack_outer', 'max')]));
		$xref->setQty_pack_tare($values->float('qty_pack_tare', ['precision' => $this->fieldAttribute('qty_pack_tare', 'precision'), 'max' => $this->fieldAttribute('qty_pack_tare', 'max')]));
		return [];
	}

	/**
	 * Update UoM Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdateUoM(WireInputData $values, ItemXrefCustomer $xref) {
		$umm = Codes\Min\Umm::instance();
		$invalidfields = [];

		$uomPricingExists = $umm->exists($values->string('uom_pricing'));

		if ($uomPricingExists) {
			$xref->setUom_pricing($values->string('uom_pricing'));
		}

		if ($uomPricingExists === false) {
			$invalidfields['uom_pricing'] = 'UoM Pricing';
		}

		$uomCustomerExists = $umm->exists($values->string('uom_customer'));

		if ($uomCustomerExists) {
			$xref->setUom_customer($values->string('uom_customer'));
		}

		if ($uomCustomerExists === false) {
			$invalidfields['uom_customer'] = 'UoM Customer';
		}
		return $invalidfields;
	}

	/**
	 * Update Pack Qty Fields
	 * @param WireInputData    $values
	 * @param ItemXrefCustomer $xref
	 * @return array
	 */
	private function _inputUpdateRounding(WireInputData $values, ItemXrefCustomer $xref) {
		$options = $this->fieldAttribute('rounding', 'options');
		$rounding = strtoupper($values->text('rounding'));

		if (array_key_exists($rounding, $options)) {
			$xref->setRounding($rounding);
			return [];
		}
		$xref->setRounding($this->fieldAttribute('rounding', 'default'));
		return [];
	}

	/**
	 * Deletes CXM Record, then recreates it
	 * Used for when modifying a CXM record key
	 * @param  ItemXrefCustomer $record
	 * @return void
	 */
	public function recreateRecord(ItemXrefCustomer $record) {
		$array = $record->toArray();
		$original = new ItemXrefCustomer();
		$original->fromArray($array);
		if (array_key_exists(ItemXrefCustomer::get_aliasproperty('custitemid'), $record->originalvalues)) {
			$original->setCustitemid($record->originalvalues[ItemXrefCustomer::get_aliasproperty('custitemid')]);
		}
		$original->setItemid($record->originalvalues[ItemXrefCustomer::get_aliasproperty('itemid')]);
		$original->delete();
		$this->updateDplus($original);

		$new = ItemXrefCustomer::new();
		$new->fromArray($array);
		$new->save();
		$record = $new;
	}

	/**
	 * Update Xref from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdateShortitem(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		
		$itemID     = $values->string('itemID');
		$custitemID = $values->string('custitemid');

		if ($this->shortitemidExists($custitemID)) {
			$xref = $this->shortitem($custitemID);

			if ($values->bool('overwrite') === false) {
				$message = "Short ItemID '$custitemID' already exists for $xref->itemid";
				$this->setResponse(Response::responseError($xref->itemid, $message));
				return false;
			}
			
			if ($xref->isNew() === false) {
				$this->recreateRecord($xref);
			}
			$xref->setDate(date('Ymd'));
			$xref->setTime(date('His'));
			$response = $this->saveAndRespond($xref);
			$this->setResponse($response);
			return $response->hasSuccess();
		}
		$xref = $this->getOrCreateXrefShortitem($itemID, $custitemID);
		$xref->setDate(date('Ymd'));
		$xref->setTime(date('His'));
		$response = $this->saveAndRespond($xref);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete Xref
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$custID = $values->string('custID');
		$custitemID = $values->string('custitemID');

		if ($this->exists($custID, $custID) === false) {
			return true;
		}
		$xref = $this->xref($custID, $custitemID);
		
		if ($this->lockrecord($xref) === false) {
			$message = self::DESCRIPTION_RECORD . " ($custID-$custitemID)  was not saved, it is locked by " . $this->recordlocker->getLockingUser($this->getRecordlockerKey($xref));
			$this->setResponse(Response::responseError("$custID-$custitemID", $message));
			return false;
		}

		$xref->delete();
		$response = $this->saveAndRespond($xref);
		$$this->setResponse($response);
		return $response->hasSuccess();

	}

/* =============================================================
	Dplus Requests
============================================================= */
	protected function _generateRequestData(Xref $xref) {
		return [
			'UPDATECXM',
			"CUSTID=$xref->custid", "CUSTITEM=$xref->custitemid", "OURITEM=$xref->itemid"
		];
	}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return Customer
	 * @param  string $custID Customer ID
	 * @return Customer
	 */
	public function customer($custID) {
		return Codes\Mar\Cmm::instance()->customer($custID);
	}

	/**
	 * Return Options for Unit of Measure
	 * @return ObjectCollection
	 */
	public function getUomOptions() {
		$filter = new Filters\Min\UnitofMeasure();
		return $filter->query->find();
	}

	/**
	 * Send PDM update for X-Ref
	 * @param  ItemXrefCustomer $xref
	 * @return void
	 */
	private function updatePdm(ItemXrefCustomer $xref) {
		$allowed = false;
		$allowed = $this->allowPdmUpdate();

		if ($xref->has_customerpricing()) {
			$allowed = $xref->getCustomerPricing()->baseprice != $xref->price_customer;
		}

		if ($allowed === false) {
			return false;
		}

		$input = new WireInput();
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$values->action      = 'update-pdm-cxm';
		$values->price       = $xref->price_customer;
		$values->custID      = $xref->custid;
		$values->custitemID  = $xref->custitemid;
		$values->itemID      = $xref->itemid;
		$this->wire('modules')->get('Pdm')->process_input($input);
	}
}
