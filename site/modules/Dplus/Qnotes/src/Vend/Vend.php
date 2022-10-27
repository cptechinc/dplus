<?php namespace Dplus\Qnotes\Vend;
// Propel ORM Library
use Propel\Runtime\ActiveQuery\ModelCriteria as Query;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dolus Models
use VendorOrderNoteQuery, VendorOrderNote;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes;

abstract class Vend extends Qnotes {
	const MODEL                = '';
	const MODEL_KEY            = 'vendorid, sequence';
	const DESCRIPTION          = 'Vendor Notes';
	const RESPONSE_TEMPLATE    = 'Vendor Note {key} was {not} {crud}';
	const TYPE                 = 'VEND';
	const TABLE                = '';

	const FIELD_ATTRIBUTES = [
		'note' => ['type' => 'text', 'cols' => 50],
	];

/* =============================================================
	Field Configs
============================================================= */
	/**
	 * Initialize Field Attributes
	 * NOTE: values may be set from configs
	 * @return void
	 */
	public function initFieldAttributes() {
		$attributes = static::FIELD_ATTRIBUTES;
		$attributes['notes']['cols'] = Configs\Ap::config()->columns_notes_pord;
		return $this->fieldAttributes = $attributes;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered By Vendor ID, Shipfrom ID
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @return Query
	 */
	public function queryVendoridShipfromid($vendorID, $shipfromID) {
		$q = $this->query();
		$q->filterByVendorid($vendorID);
		$q->filterByShipfromid($shipfromID);
		return $q;
	}
/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @return bool
	 */
	public function notesExist($vendorID, $shipfromID) {
		$q = $this->queryVendoridShipfromid($vendorID, $shipfromID);
		$q->filterByShipfromid($shipfromID);
		return boolval($q->count());

	}

	/**
	 * Return Note Lines
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @return array
	 */
	public function getNotesArray($vendorID, $shipfromID) {
		$class = $this->modelClassName();
		$q = $this->queryVendoridShipfromid($vendorID, $shipfromID);
		$q->select($class::aliasproperty('note'));
		return $q->find()->toArray();
	}

	/**
	 * Return Note Line
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @param  int    $line        Line Number
	 * @return Model
	 */
	public function noteLine($vendorID, $shipfromID, $line = 1) {
		$q = $this->queryVendoridShipfromid($vendorID, $shipfromID);
		$q->filterBySequence($line);
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New VendorOrderNote
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @return Model
	 */
	public function new($vendorID, $shipfromID) {
		$class = $this->modelClassName();

		$note = $class::new();
		$note->setVendorid($vendorID);
		$note->setShipfromID($shipfromID);
		$note->generateKey2(); // PK
		$note->setSequence(1); // PK
		$note->setDummy('P');
		return $note;
	}

/* =============================================================
	CRUD Delete Functions
============================================================= */
	/**
	 * Delete Notes
	 * @param  string $vendorID    Vendor ID
	 * @param  string $shipfromID  Shipfrom ID
	 * @return bool
	 */
	public function deleteNotes($vendorID, $shipfromID) {
		$q = $this->queryVendoridShipfromid($vendorID, $shipfromID);
		if ($q->count() === 0) {
			return true;
		}
		return $q->delete();
	}

/* =============================================================
	CRUD Processing
============================================================= */
	// /**
	//  * Write Notes from Input Data
	//  * @param  WireInput $input Input Data
	//  * @return bool
	//  */
	// protected function _inputUpdate(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	// 	$values = $input->$rm;
	// 	$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
	// 	$this->deleteNotes($id);
	// 	$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
	// 	$savedLines = [];
	//
	// 	foreach ($noteLines as $key => $line) {
	// 		$sequence = $key + 1;
	// 		$note = $this->new($id);
	// 		$note->generateKey2(); // PK
	// 		$note->setSequence($sequence); // PK
	// 		$note->setNote($line);
	// 		$note->setDate(date('Ymd'));
	// 		$note->setTime(date('His').'00');
	// 		$savedLines[$sequence] = boolval($note->save());
	// 	}
	// 	$response = $this->updateAndRespond($note, $savedLines);
	// 	$this->setResponse($response);
	// 	return $response->hasSuccess();
	// }
	//
	// /**
	//  * Process Written Lines, update Dplus cobol
	//  * @param  VendorOrderNote $note
	//  * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	//  * @return Response
	//  */
	// private function updateAndRespond(VendorOrderNote $note, array $savedLines = []) {
	// 	$response = new Response();
	// 	$response->setKey($note->id);
	// 	$response->setAction(Response::CRUD_UPDATE);
	//
	// 	if (in_array(false, $savedLines)) {
	// 		$errorLines =
	// 		array_filter($savedLines, function($value, $key) {
	// 			return  $value == false;
	// 		}, ARRAY_FILTER_USE_BOTH);
	//
	// 		$response->addMsgReplacement('{lines}', implode(", ", array_keys($errorLines)));
	//
	// 		if (sizeof($errorLines)) {
	// 			$response->setError(true);
	// 		}
	// 	} else {
	// 		$response->setSuccess(true);
	// 		$response->addMsgReplacement('{lines}', '');
	// 	}
	// 	$response->buildMessage(static::RESPONSE_TEMPLATE);
	//
	// 	if ($response->hasSuccess()) {
	// 		$this->updateDplus($note);
	// 	}
	// 	return $response;
	// }
	//
	// /**
	//  * Delete Notes from Input Data
	//  * @param  WireInput $input Input Data
	//  * @return bool
	//  */
	// protected function _inputDelete(WireInput $input) {
	// 	$rm = strtolower($input->requestMethod());
	// 	$values = $input->$rm;
	// 	$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
	//
	// 	$note = $this->new($id);
	// 	$response = $this->deleteAndRespond($note);
	//
	// 	$this->setResponse($response);
	// 	return $response->hasSuccess();
	// }
	//
	// /**
	//  * Delete note, update Dplus cobol
	//  * @param  VendorOrderNote $note
	//  * @return Response
	//  */
	// protected function deleteAndRespond(VendorOrderNote $note) {
	// 	$success = $this->deleteNotes($note->id);
	//
	// 	$response = new Response();
	// 	$response->setKey($note->id);
	// 	$response->setAction(Response::CRUD_DELETE);
	// 	$response->setSuccess($success);
	// 	$response->setError($success === false);
	// 	$response->addMsgReplacement('{lines}', '');
	// 	$response->buildMessage(static::RESPONSE_TEMPLATE);
	// 	if ($response->hasSuccess()) {
	// 		$this->updateDplus($note);
	// 	}
	// 	return $response;
	// }
}
