<?php namespace Dplus\Codes\Po;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use PoConfirmCodeQuery, PoConfirmCode;
// ProcessWire
use ProcessWire\WireData;
// Dplus Codes
use Dplus\Codes\Base;

/**
 * Class that handles the CRUD of the CNFM code table
 */
class Cnfm extends Base {
	const MODEL              = 'PoConfirmCode';
	const MODEL_KEY          = 'id';
	const DESCRIPTION        = 'PO Confirmation Code';
	const DESCRIPTION_RECORD = 'PO Confirmation Code';
	const TABLE              = 'po_confirm_code';
	const RECORDLOCKER_FUNCTION = 'cxm';

	private static $instance;

	/**
	 * Return the Max Length of characters for the code
	 * NOTE: Used for the JS
	 * @return int
	 */
	public function codeMaxLength() {
		return PoConfirmCode::MAX_LENGTH_CODE;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Purchase Order Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(PoConfirmCode::get_aliasproperty('id'));
		return $q->find()->toArray();
	}

	/**
	 * Return the Code records from Database
	 * @return ObjectCollection
	 */
	public function codes() {
		$q = $this->getQueryClass();
		return $q->find();
	}

	/**
	 * Return the Code records from Database filtered by ProductLne ID
	 * @param  string $id
	 * @return ObjectCollection
	 */
	public function code($id) {
		$q = $this->query();
		return $q->findOneById($id);
	}

	/**
	 * Returns if Code Exists
	 * @param  string $id
	 * @return bool
	 */
	public function exists($stock) {
		$q = $this->query();
		return boolval($q->filterById($stock)->count());
	}

/* =============================================================
	CRUD Processing
============================================================= */
	// /**
	//  * Takes Input, validates it's for one of the code tables
	//  * Processes it, and if updated sends request to dplus
	//  *
	//  * NOTE: If an existing code is more than PoConfirmCode::MAX_LENGTH_CODE, we will allow editing
	//  * but we won't allow creation of a code with more than allowed characters we will trim it.
	//  *
	//  * @param  WireInput $input Input
	//  * @return void
	//  */
	// public function process_input(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	//
	// 	$table = $input->$rm->text('table');
	// 	$code  = $input->$rm->text('code');
	//
	// 	$q = $this->get_query();
	// 	$q->filterByCode($code);
	//
	// 	if ($q->count()) {
	// 		$record = $q->findOne();
	// 	} else {
	// 		$code  = $input->$rm->text('code', array('maxLength' => PoConfirmCode::MAX_LENGTH_CODE));
	// 		$record = new PoConfirmCode();
	// 		$record->setCode($code);
	// 	}
	//
	// 	if ($input->$rm->text('action') == 'remove-code') {
	// 		$record->delete();
	// 	} else {
	// 		$description = $input->$rm->text('description');
	// 		$record->setDescription($description);
	// 		$record->setDate(date('Ymd'));
	// 		$record->setTime(date('His'));
	// 		$record->setDummy('P');
	// 	}
	//
	// 	$this->wire('session')->response_codetable = $this->save_and_process_response($table, $code, $record);
	// }
	//
	// /**
	//  * Returns CodeTablesResponse based on the outcome of the database save
	//  *
	//  * @param  string $table  Table Code
	//  * @param  string $code   Code being added
	//  * @param  bool   $is_new Was the Record in the database before Save?
	//  * @param  bool   $saved  Was the Record Saved?
	//  * @return CodeTablesResponse
	//  */
	// protected function save_and_process_response($table, $code, PoConfirmCode $record) {
	// 	$is_new = $record->isDeleted() ? false : $record->isNew();
	// 	$saved  = $record->isDeleted() ? $record->isDeleted() : $record->save();
	//
	// 	$response = new CodeTablesResponse();
	// 	$response->set_key($code);
	// 	$message = self::DESCRIPTION_RECORD . " ($code) was ";
	//
	// 	if ($saved) {
	// 		$response->set_success(true);
	// 	} else {
	// 		$response->set_error(true);
	// 		$message .= "not ";
	// 	}
	//
	// 	if ($is_new) {
	// 		$message .= 'added';
	// 		$response->set_action(CodeTablesResponse::CRUD_CREATE);
	// 	} elseif ($record->isDeleted()) {
	// 		$message .= 'deleted';
	// 		$response->set_action(CodeTablesResponse::CRUD_DELETE);
	// 	} else {
	// 		$message .= 'updated';
	// 		$response->set_action(CodeTablesResponse::CRUD_UPDATE);
	// 	}
	//
	// 	$response->set_message($message);
	//
	// 	if ($response->has_success()) {
	// 		$this->wire('modules')->get('CodeTables')->update_dplus_cobol($table, $code);
	// 	}
	// 	return $response;
	// }


}
