<?php namespace Dplus\Min\Inmain\Itm\Options;
// Dplus Models
use InvOptCodeQuery, InvOptCode;
use MsaSysopCode, SysopOptionalCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Databases
use Dplus\Databases\Connectors\Dplus as DbDplus;
// Dplus Msa
use Dplus\Msa\Sysop;
use Dplus\Msa\SysopOptions;
// Dplus Itm
use Dplus\Min\Inmain\Itm\Response;

/**
 * Options
 * Manages CRUD operations for the InvOptCode Records
 */
class Codes extends WireData {
	const MODEL              = 'InvOptCode';
	const MODEL_KEY          = 'itemid, id';
	const DESCRIPTION        = 'Item Options';
	const RESPONSE_TEMPLATE  = 'Item {itemid} Option {sysop} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'itm';
	const SYSTEM                = 'IN';

	public function __construct() {
		$this->sessionID = session_id();
	}

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

	/**
	 * Return Query
	 * @return InvOptCodeQuery
	 */
	public function query() {
		return InvOptCodeQuery::create();
	}

	/**
	 * Return Array ready for JSON
	 * @param  InvOptCode  $code Code
	 * @return array
	 */
	public function codeJson($sysopCode, InvOptCode $code = null) {
		$code  = empty($code) === false ? $code : $this->new('', $sysopCode);
		$sysop = $this->getSysop()->code(self::SYSTEM, $sysopCode);

		return [
			'code'        => $code->code,
			'description' => $code->description,
			'sysop'      => [
				'sysop'       => $code->sysop,
				'description' => $sysop->description
			]
		];
	}

/* =============================================================
	Create, Read Functions
============================================================= */
	/**
	 * Return if Item has Itm Dimension Record
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return bool
	 */
	public function exists($itemID, $sysop) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterBySysop($sysop);
		return boolval($q->count());
	}

	/**
	 * Return Option Code
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return InvOptCode
	 */
	public function code($itemID, $sysop) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterBySysop($sysop);
		return $q->findOne();
	}

	/**
	 * Return new InvOptCode
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Option Code
	 * @return InvOptCode
	 */
	public function new($itemID, $sysop) {
		$opt = new InvOptCode();
		$opt->setItemid($itemID);
		if ($sysop) {
			$opt->setSysop($sysop);
		}
		return $opt;
	}

	/**
	 * Return Existing or New Sysop code Value for Item
	 * @param  string $itemID Item ID
	 * @param  string $sysop  System Optional Code
	 * @return InvOptCode
	 */
	public function getOrCreate($itemID, $sysop) {
		if ($this->exists($itemID, $sysop)) {
			return $this->code($itemID, $sysop);
		}
		return $this->new($itemID, $sysop);
	}

/* =============================================================
	Input Functions
============================================================= */
	/**
	 * Process Input Data
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
			case 'delete':
				$this->deleteInput($input);
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
		return $this->updateInputCode($input);
	}

	/**
	 * Update Itm Option Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function updateInputCode(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->string('itemID');
		$sysop  = $values->string('sysop');
		$code   = $values->string('code');

		$sysopM = $this->getSysop();

		if ($sysopM->exists(self::SYSTEM, $sysop) === false) {
			$msg = " Sysop $sysop Not found";
			$this->setResponse(Response::responseError($itemID, $msg));
			return false;
		}
		$sysOption  = $sysopM->code(self::SYSTEM, $sysop);
		$itmOptCode = $this->getOrCreate($itemID, $sysop);
		$itmOptCode->setSysopdesc($sysOption->description);
		$itmOptCode->setDate(date('Ymd'));
		$itmOptCode->setTime(date('His'));
		$itmOptCode->setDummy('P');

		$isValid = $this->updateCodeUsingSysopRules($sysOption, $itmOptCode);

		if ($isValid === false) {
			return false;
		}

		$response = $this->saveAndRespond($itmOptCode);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Update Code Using Sysop Rules
	 * @param  MsaSysopCode $sysOption
	 * @param  InvOptCode   $itmOptCode
	 * @return bool
	 */
	private function updateCodeUsingSysopRules(MsaSysopCode $sysOption, InvOptCode $itmOptCode) {
		$input = $this->wire('input');
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->string('itemID');
		$sysop  = $values->string('sysop');
		$code   = $values->string('code');

		if ($sysOption->force() && $sysop == '') {
			$msg = "Sysop $sysop is Required";
			$this->setResponse(Response::responseError($itemID, $msg));
			return false;
		}

		$itmOptCode->setCode('');
		$itmOptCode->setDescription('');

		$optManager = $this->getSysopOptions();

		if ($optManager->exists(self::SYSTEM, $sysop, $code)) {
			$sysOptOption = $optManager->code(self::SYSTEM, $sysop, $code);
			$itmOptCode->setCode($code);
			$itmOptCode->setDescription($sysOptOption->description);
			return true;
		}

		if ($sysOption->validate() === false) {
			$itmOptCode->setCode($code);
			return true;
		}

		if ($sysOption->validate() && $code != '') {
			if ($optManager->exists(self::SYSTEM, $sysop, $code) === false) {
				$msg = "Sysop $sysop Code $code not found";
				$this->setResponse(Response::responseError($itemID, $msg));
				return false;
			}
		}
		return true;
	}

	/**
	 * Update Itm Dimension, Itm Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	private function deleteInput(WireInput $input) {
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
		return $this->deleteInputCode($input);
	}

	/**
	 * Delete Itm Option Code
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	private function deleteInputCode(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$itemID = $values->string('itemID');
		$sysop  = $values->string('sysop');
		$code   = $values->string('code');

		$sysopM = $this->getSysop();

		if ($sysopM->exists(self::SYSTEM, $sysop) === false) {
			return true;
		}

		if ($this->exists($itemID, $sysop) === false) {
			return true;
		}

		$itmOptCode = $this->code($itemID, $sysop);
		$itmOptCode->delete();
		$response = $this->saveAndRespond($itmOptCode);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

/* =============================================================
	CRUD Response Functions
============================================================= */
	/**
	 * Returns Response based on the outcome of the database save
	 * @param  InvOptCode $code          Record to record response of database save
	 * @param  array      $invalidfields Input fields that require attention
	 * @return Response
	 */
	private function saveAndRespond(InvOptCode $code, array $invalidfields = []) {
		$is_new = $code->isDeleted() ? false : $code->isNew();
		$saved  = $code->isDeleted() ? $code->isDeleted() : $code->save();

		$response = new Response();
		$response->setItemID($code->itemid);
		$response->setKey("{$code->itemid}-{$code->sysop}");

		if ($saved) {
			$response->setSuccess(true);
		} else {
			$response->setError(true);
		}

		if ($is_new) {
			$response->setAction(Response::CRUD_CREATE);
		} elseif ($code->isDeleted()) {
			$response->setAction(Response::CRUD_CLEAR);
		} else {
			$response->setAction(Response::CRUD_UPDATE);
		}
		$response->addMsgReplacement('{sysop}', $code->sysop);
		$response->addMsgReplacement('{code}', $code->code);
		$response->buildMessage(self::RESPONSE_TEMPLATE);

		if ($response->hasSuccess() && empty($invalidfields)) {
			$this->requestUpdate($code->itemid, $code->sysop);
		}
		$response->setFields($invalidfields);
		return $response;
	}

	/**
	 * Set Session Response
	 * @param Response $response
	 */
	public function setResponse(Response $response) {
		$this->wire('session')->setFor('response', 'itm-options', $response);
	}

	/**
	 * Get Session Response
	 * @return Response|null
	 */
	public function getResponse() {
		return $this->wire('session')->getFor('response', 'itm-options');
	}

	/**
	 * Delete Response
	 * @return void
	 */
	public function deleteResponse() {
		return $this->wire('session')->removeFor('response', 'itm-options');
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
	 * Request Update for ITM Option Code
	 * @param  string $itemID  Item ID
	 * @param  string $sysop   Sysop Option ID
	 * @return void
	 */
	private function requestUpdate($itemID, $sysop) {
		$data = ['UPDATEITMOPT', "ITEMID=$itemID", "OPTCODE=$sysop"];
		$this->requestDplus($data);
	}

	/**
	 * Send Request to Dplus
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

/* =============================================================
	Supplemental Functions
============================================================= */
	public function getSysop() {
		return Sysop::getInstance();
	}

	public function getSysopOptions() {
		return SysopOptions::getInstance();
	}
}
