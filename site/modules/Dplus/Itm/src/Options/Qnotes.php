<?php namespace Dplus\Min\Inmain\Itm\Options;
// Dolus Models
use InvOptCodeNoteQuery, InvOptCodeNote;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes as QnotesBase;

class Qnotes extends QnotesBase {
	const MODEL                = 'InvOptCodeNote';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Inventory Optional Code Notes';
	const RESPONSE_TEMPLATE    = '{itemid} Optional Code Note {type} was {not} {crud}';

	const FIELD_ATTRIBUTES = [
		'note' => ['type' => 'text', 'cols' => 60],
	];

	protected static $instance;

	public function notesJson($itemID, $type) {
		$note = $this->noteLine($itemID, $type);
		$json = [
			'itemid' => $note->itemid,
			'type'   => $note->type,
			'note'   => $this->getNotesImploded($itemID, $type)
		];
		return $json;
	}

/* =============================================================
	Query Functions
============================================================= */
	/**
	 * Return Query Filtered By item ID, Type
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @return InvOptCodeNoteQuery
	 */
	public function queryItemidType($itemID, $type) {
		$q = $this->query();
		$q->filterByItemid($itemID);
		$q->filterByType($type);
		return $q;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @return bool
	 */
	public function notesExist($itemID, $type) {
		$q = $this->queryItemidType($itemID, $type);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @return array
	 */
	public function getNotesArray($itemID, $type) {
		$q = $this->queryItemidType($itemID, $type);
		$q->select(InvOptCodeNote::aliasproperty('note'));
		return $q->find()->toArray();
	}

	public function getNotesImploded($itemID, $type, $glue = "\n") {
		return implode($glue, $this->getNotesArray($itemID, $type));
	}

	/**
	 * Return Note Line
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @param  int    $line   Line Number
	 * @return InvOptCodeNote
	 */
	public function noteLine($itemID, $type, $line = 1) {
		$q = $this->queryItemidType($itemID, $type);
		$q->filterBySequence($line);
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New InvOptCodeNote
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @return InvOptCodeNote
	 */
	public function new($itemID, $type) {
		$note = InvOptCodeNote::new();
		$note->setItemid($itemID);
		$note->setType($type);
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
	 * @param  string $id    Note Line
	 * @return bool
	 */
	public function deleteNotes($itemID, $type) {
		$q = $this->queryItemidType($itemID, $type);
		if ($q->count() === 0) {
			return true;
		}
		return $q->delete();
	}

// /* =============================================================
// 	CRUD Processing
// ============================================================= */
// 	/**
// 	 * Write Notes from Input Data
// 	 * @param  WireInput $input Input Data
// 	 * @return bool
// 	 */
// 	protected function _inputUpdate(WireInput $input) {
// 		$rm = strtolower($input->requestMethod());
// 		$values = $input->$rm;
// 		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
// 		$this->deleteNotes($id);
// 		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
// 		$savedLines = [];
//
// 		foreach ($noteLines as $key => $line) {
// 			$sequence = $key + 1;
// 			$note = $this->new($id);
// 			$note->generateKey2(); // PK
// 			$note->setSequence($sequence); // PK
// 			$note->setNote($line);
// 			$note->setDate(date('Ymd'));
// 			$note->setTime(date('His').'00');
// 			$savedLines[$sequence] = boolval($note->save());
// 		}
// 		$response = $this->updateAndRespond($note, $savedLines);
// 		$this->setResponse($response);
// 		return $response->hasSuccess();
// 	}
//
// 	/**
// 	 * Process Written Lines, update Dplus cobol
// 	 * @param  InvOptCodeNote $note
// 	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
// 	 * @return Response
// 	 */
// 	private function updateAndRespond(InvOptCodeNote $note, array $savedLines = []) {
// 		$response = new Response();
// 		$response->setKey($note->id);
// 		$response->setAction(Response::CRUD_UPDATE);
//
// 		if (in_array(false, $savedLines)) {
// 			$errorLines =
// 			array_filter($savedLines, function($value, $key) {
// 				return  $value == false;
// 			}, ARRAY_FILTER_USE_BOTH);
//
// 			$response->addMsgReplacement('{lines}', implode(", ", array_keys($errorLines)));
//
// 			if (sizeof($errorLines)) {
// 				$response->setError(true);
// 			}
// 		} else {
// 			$response->setSuccess(true);
// 			$response->addMsgReplacement('{lines}', '');
// 		}
// 		$response->buildMessage(static::RESPONSE_TEMPLATE);
//
// 		if ($response->hasSuccess()) {
// 			$this->updateDplus($note);
// 		}
// 		return $response;
// 	}
//
// 	/**
// 	 * Delete Notes from Input Data
// 	 * @param  WireInput $input Input Data
// 	 * @return bool
// 	 */
// 	protected function _inputDelete(WireInput $input) {
// 		$rm = strtolower($input->requestMethod());
// 		$values = $input->$rm;
// 		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
//
// 		$note = $this->new($id);
// 		$response = $this->deleteAndRespond($note);
//
// 		$this->setResponse($response);
// 		return $response->hasSuccess();
// 	}
//
// 	/**
// 	 * Delete note, update Dplus cobol
// 	 * @param  InvOptCodeNote $note
// 	 * @return Response
// 	 */
// 	protected function deleteAndRespond(InvOptCodeNote $note) {
// 		$success = $this->deleteNotes($note->id);
//
// 		$response = new Response();
// 		$response->setKey($note->id);
// 		$response->setAction(Response::CRUD_DELETE);
// 		$response->setSuccess($success);
// 		$response->setError($success === false);
// 		$response->addMsgReplacement('{lines}', '');
// 		$response->buildMessage(static::RESPONSE_TEMPLATE);
// 		if ($response->hasSuccess()) {
// 			$this->updateDplus($note);
// 		}
// 		return $response;
// 	}
}