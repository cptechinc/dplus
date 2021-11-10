<?php namespace Dplus\Min\Inmain\Itm\Options;
// Propel ORM Library
use Propel\Runtime\ActiveRecord\ActiveRecordInterface;
// Dolus Models
use InvOptCodeNoteQuery, InvOptCodeNote;
use MsaSysopCode;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes as QnotesBase;
use Dplus\Qnotes\Response;
// Dplus Msa
use Dplus\Msa\Sysop;
// Dplus Itm
use Dplus\Min\Inmain\Itm\Response as ResponseItm;


class Qnotes extends QnotesBase {
	const MODEL                = 'InvOptCodeNote';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Inventory Optional Code Notes';
	const RESPONSE_TEMPLATE    = '{itemid} Optional Code Note {type} was {not} {crud}';
	const SYSTEM = 'IN';

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

	/**
	 * Return Notes as One Text Line
	 * @param  string $itemID Item ID
	 * @param  string $type   Note Type
	 * @param  string $glue   Concatenator Character
	 * @return string
	 */
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

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Write Notes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function _inputUpdate(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$sysopM = $this->getSysop();

		if ($sysopM->isNote(self::SYSTEM, $values->text('sysop')) === false) {
			return false;
		}
		$sysop  = $sysopM->code(self::SYSTEM, $values->text('sysop'));
		$itemID = $values->text('itemID');
		$type   = $sysopM->notecode(self::SYSTEM, $values->text('sysop'));

		if ($sysop->force() && empty($values->textarea('note'))) {
			$responseQnotes = Response::responseError("$type Notes are required");
			$responseQnotes->setAction(Response::CRUD_DELETE);
			$response = $this->responseItmFromQnoteResponse($responseQnotes, $sysop, $itemID);
			$codesM = Codes::getInstance();
			$codesM->setResponse($response);
			return false;
		}

		$this->deleteNotes($itemID, $type);

		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($itemID, $type);
			$note->generateKey2(); // PK
			$note->setSequence($sequence); // PK
			$note->setNote($line);
			$note->setDescription(implode(' ', [strtoupper($type), '-', $sysop->description]));
			$note->setDate(date('Ymd'));
			$note->setTime(date('His').'00');
			$savedLines[$sequence] = boolval($note->save());
		}

		$responseQnotes = $this->updateAndResponse($note, $savedLines);
		$response = $this->responseItmFromQnoteResponse($responseQnotes, $sysop, $itemID);
		$codesM = Codes::getInstance();
		$codesM->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Process Written Lines, update Dplus cobol
	 * @param  InvOptCodeNote $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(InvOptCodeNote $note, array $savedLines = []) {
		$response = new Response();
		$response->setKey("$note->itemid-$note->type");
		$response->setAction(Response::CRUD_UPDATE);

		if (in_array(false, $savedLines)) {
			$errorLines =
			array_filter($savedLines, function($value, $key) {
				return  $value == false;
			}, ARRAY_FILTER_USE_BOTH);

			if (sizeof($errorLines)) {
				$response->setError(true);
			}
		} else {
			$response->setSuccess(true);
		}
		$response->addMsgReplacement('{itemid}', $note->itemid);
		$response->addMsgReplacement('{type}', $note->type);
		$response->buildMessage(static::RESPONSE_TEMPLATE);

		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}

	/**
	 * Delete Notes from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function _inputDelete(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$sysopM = $this->getSysop();
		$itemID = $values->text('itemID');
		$type   = $sysopM->notecode(self::SYSTEM, $values->text('sysop'));
		$sysop  = $sysopM->code(self::SYSTEM, $values->text('sysop'));

		$note = $this->new($itemID, $type);
		$responseQnotes = $this->deleteAndRespond($note);
		$response = $this->responseItmFromQnoteResponse($responseQnotes, $sysop, $itemID);

		$codesM = Codes::getInstance();
		$codesM->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete note, update Dplus cobol
	 * @param  InvOptCodeNote $note
	 * @return Response
	 */
	protected function deleteAndRespond(InvOptCodeNote $note) {
		$success = $this->deleteNotes($note->itemid, $note->type);

		$response = new Response();
		$response->setKey("$note->itemid-$note->type");
		$response->setAction(Response::CRUD_DELETE);
		$response->setSuccess($success);
		$response->setError($success === false);
		$response->addMsgReplacement('{itemid}', $note->itemid);
		$response->addMsgReplacement('{type}', $note->type);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}
/* =============================================================
	Response Functions
============================================================= */
	/**
	 * Return Itm Response
	 * @param  Response     $response
	 * @param  MsaSysopCode $sysop     System Optional Code
	 * @param  string       $itemID    Item ID
	 * @return ResponseItm
	 */
	protected function responseItmFromQnoteResponse(Response $response, MsaSysopCode $sysop, $itemID) {
		$r = new ResponseItm();
		$r->setKey("$itemID-$sysop->code");
		$r->setAction($response->action);
		$r->setItemid($itemID);
		$r->setSuccess($response->hasSuccess());
		$r->setError($response->hasError());
		$r->setMessage($response->message);
		return $r;
	}
	
/* =============================================================
	Dplus Requests
============================================================= */
	/**
	 * Return Data needed for Dplus to UPDATE the Qnote
	 * @param  string $notetype Note Type @see WarehouseNote::TYPES
	 * @param  string $key2     Key 2
	 * @param  string $form     Form e.g YNNN
	 * @return array
	 */
	public function writeRqstData(ActiveRecordInterface $note) {
		$data = parent::writeRqstData($note);
		$data[] = "SYSCODE=".self::SYSTEM;
		return $data;
	}

/* =============================================================
	Supplemental Functions
============================================================= */
	public function getSysop() {
		return Sysop::getInstance();
	}
}
