<?php namespace Dplus\Qnotes\CustType;
// Dolus Models
use NoteArCustTypeQuery, NoteArCustType;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Mar\Ctm;
// Dplus Qnotes
use Dplus\Qnotes\Qnotes;
use Dplus\Qnotes\Response;


class Base extends Qnotes {
	const MODEL                = 'NoteArCustType';
	const MODEL_KEY            = 'id';
	const MODEL_TABLE          = 'notes_cust_type';
	const DESCRIPTION          = 'Cust Type Note';
	const RESPONSE_TEMPLATE    = 'Cust Type {type} Note  {key} was {not} {crud}';
	const TYPE                 = 'ICTP';
	const TYPE_DESCRIPTION     = 'Invoice';
	const FIELD_ATTRIBUTES = [
		'code' => ['type' => 'text', 'maxlength' => Ctm::FIELD_ATTRIBUTES['code']['maxlength']],
		'note' => ['type' => 'text', 'cols' => 35],
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
		$this->fieldAttributes = static::FIELD_ATTRIBUTES;
		$configAR = Configs\Ar::config();
		$this->fieldAttributes['note']['cols'] = $configAR->columns_notes_invoice;
	}

	/**
	 * Return Record Description
	 * @return string
	 */
	public function dbDescription() {
		$desc = static::TYPE_DESCRIPTION;
		return "Cust Type $desc Notes";
	}

	/**
	 * Return Record Description
	 * @return string
	 */
	public function typeDescription() {
		return static::TYPE_DESCRIPTION;
	}


/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $id   Customer Type Code
	 * @param  string $type Note Type @see NoteArCustType::TYPES
	 * @return bool
	 */
	public function notesExist($id) {
		$q = $this->query();
		$q->filterByTypecode($id);
		$q->filterByType(static::TYPE);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $id Customer Type Code
	 * @return array
	 */
	public function getNotesArray($id) {
		$q = $this->query();
		$q->select(NoteArCustType::aliasproperty('note'));
		$q->filterByTypecode($id);
		$q->filterByType(static::TYPE);
		return $q->find()->toArray();
	}

	/**
	 * Return Note Line
	 * @param  string $id    Customer Type Code
	 * @param  int    $line  Line Number
	 * @return NoteArCustType
	 */
	public function noteLine($id, $line = 1) {
		$q = $this->query();
		$q->filterByTypecode($id);
		$q->filterByType(static::TYPE);
		$q->filterBySequence($line);
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New NoteArCustType
	 * @param  string $id  Customer Type Code
	 * @return NoteArCustType
	 */
	public function new($id) {
		$note = new NoteArCustType();
		$note->setTypecode($id);
		$note->setType(static::TYPE);
		$note->setDescription($this->dbDescription());
		$note->generateKey2();
		$note->setSequence(1); // PK
		$note->setDummy('P');
		return $note;
	}

/* =============================================================
	CRUD Delete Functions
============================================================= */
	/**
	 * Delete Notes
	 * @param  string $id  Customer Type Code
	 * @return bool
	 */
	public function deleteNotes($id) {
		$q = $this->query();
		$q->filterByTypecode($id);
		$q->filterByType(static::TYPE);
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
		$id = $values->text('code');
		$this->deleteNotes($id);

		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($id);
			$note->setSequence($sequence); // PK
			$note->generateKey2();
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
		$response->setKey($note->typecode);
		$response->setAction(Response::CRUD_UPDATE);

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
		$response->addMsgReplacement('{type}', static::TYPE_DESCRIPTION);
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
		$id = $values->text('code');

		$note = $this->new($id);
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
		$success = $this->deleteNotes($note->code);

		$response = new Response();
		$response->setKey($note->code);
		$response->setAction(Response::CRUD_DELETE);
		$response->setSuccess($success);
		$response->setError($success === false);
		$response->addMsgReplacement('{lines}', '');
		$response->buildMessage(static::RESPONSE_TEMPLATE);
		if ($response->hasSuccess()) {
			$this->updateDplus($note);
		}
		return $response;
	}
}
