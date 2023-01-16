<?php namespace Dplus\Qnotes\CustType;
// Dolus Models
use NoteArCustType;
use ArCustTypeCode;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes;
use Dplus\Qnotes\Response;

class AbstractCustType extends Qnotes {
	const MODEL                = 'NoteArCustType';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Customer Type Notes';
	const RESPONSE_TEMPLATE    = 'Customer Type Note {artypecode} {key} was {not} {crud}';
	const TYPE                 = '';

	const FIELD_ATTRIBUTES = [
		'artypecode' => ['type' => 'text', 'maxlength' => ArCustTypeCode::MAX_LENGTH_CODE],
		'note' => ['type' => 'text', 'cols' => 35],
	];

	/**
	 * Return Description of Customer Type Code Note Type
	 * @return string
	 */
	public function getNotetypeDescription() {
		return NoteArCustType::get_type_description(static::TYPE);
	}

	/**
	 * Return Database Description of Customer Type Code Note Type
	 * @return string
	 */
	public function getNotetypeDbDescription() {
		$description = ucwords($this->getNotetypeDescription());
		return "Cust Type $description Notes";
	}

/* =============================================================
	Query Functions
============================================================= */
	public function queryType() {
		$q = $this->query();
		$q->filterByType(static::TYPE);
		return $q;
	}

	public function queryTypeArCustType($code) {
		$q = $this->queryType();
		$q->filterByCustomertype($code);
		return $q;
	} 

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $code Note ID
	 * @return bool
	 */
	public function notesExist($code) {
		$q = $this->queryTypeArCustType($code);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $code Note ID
	 * @return array
	 */
	public function getNotesArray($code) {
		$q = $this->queryTypeArCustType($code);
		$q->select(NoteArCustType::aliasproperty('note'));
		return $q->find()->toArray();
	}

	/**
	 * Return Note Line
	 * @param  string $code    Note ID
	 * @param  int    $line  Line Number
	 * @return NoteArCustType
	 */
	public function noteLine($code, $line = 1) {
		$q = $this->queryTypeArCustType($code);
		$q->filterById($code);
		$q->filterBySequence($line);
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New NoteArCustType
	 * @param  string $code  Note ID
	 * @return NoteArCustType
	 */
	public function new($code = '') {
		$note = NoteArCustType::new();
		if ($code && $code != 'new') {
			$code = $this->wire('sanitizer')->string($code);
			$note->setCustomertype($code);
			$note->setKey2($code);
		}
		$note->setType(static::TYPE);
		$note->setDescription($this->getNotetypeDbDescription());
		$note->setSequence(1); // PK
		$note->setDummy('P');
		$note->setDate(date('Ymd'));
		$note->setTime(date('His').'00');
		return $note;
	}

/* =============================================================
	CRUD Delete Functions
============================================================= */
	/**
	 * Delete Notes
	 * @param  string $code    Note Line
	 * @return bool
	 */
	public function deleteNotes($code) {
		$q = $this->queryTypeArCustType($code);

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
		$code     = $values->string('artypecode');
		$this->deleteNotes($code);
		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($code);
			$note->setSequence($sequence); // PK
			$note->setNote($line);
			$note->setDate(date('Ymd'));
			$note->setTime(date('His').'00');
			$savedLines[$sequence] = boolval($note->save());
		}
		$response = $this->updateAndRespond($note, $savedLines);
		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Process Written Lines, update Dplus cobol
	 * @param  NoteArCustType $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(NoteArCustType $note, array $savedLines = []) {
		$response = new Response();
		$response->setKey($note->customertype);
		$response->setAction(Response::CRUD_UPDATE);
		$response->setType(static::TYPE);

		if (in_array(false, $savedLines)) {
			$errorLines =
			array_filter($savedLines, function($value, $key) {
				return  $value == false;
			}, ARRAY_FILTER_USE_BOTH);

			$response->addMsgReplacement('{lines}', implode(", ", array_keys($errorLines)));

			if (sizeof($errorLines)) {
				$response->setError(true);
			}
		} else {
			$response->setSuccess(true);
			$response->addMsgReplacement('{lines}', '');
		}
		$response->addMsgReplacement('{artypecode}', $note->customertype);
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
		$code   = $values->string('artypecode');

		$note = $this->new($code);
		$response = $this->deleteAndRespond($note);

		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete note, update Dplus cobol
	 * @param  NoteArCustType $note
	 * @return Response
	 */
	protected function deleteAndRespond(NoteArCustType $note) {
		$success = $this->deleteNotes($note->customertype);

		$response = new Response();
		$response->setKey($note->customertype);
		$response->setAction(Response::CRUD_DELETE);
		$response->setSuccess($success);
		$response->setError($success === false);
		$response->setType(static::TYPE);
		$response->addMsgReplacement('{lines}', '');
		$response->addMsgReplacement('{artypecode}', $note->customertype);
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}
}
