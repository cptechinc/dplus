<?php namespace Dplus\Qnotes;
// Dolus Models
use NotePredefinedQuery, NotePredefined;
// ProcessWire
use ProcessWire\WireInput;
// Dplus
use Dplus\RecordLocker\UserFunction as Locker;

class Noce extends Qnotes {
	const MODEL                = 'NotePredefined';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Pre-Defined Notes';
	const RESPONSE_TEMPLATE    = 'Pre-Defined Note {key} was {not} {crud}';
	const TYPE                 = 'NOCE';

	const FIELD_ATTRIBUTES = [
		'code' => ['type' => 'text', 'label' => 'Code', 'maxlength' => NotePredefined::MAX_LENGTH_CODE],
		'note' => ['type' => 'text', 'label' => 'Note', 'cols' => 50],
	];

	const FILTERABLE_FIELDS = ['code', 'note'];

	public function __construct() {
		parent::__construct();
		$this->recordlocker = new Locker();
		$this->recordlocker->setFunction(strtolower(self::TYPE));
		$this->recordlocker->setUser($this->wire('user'));
	}

	/**
	 * Return List of filterable fields
	 * @return array
	 */
	public function filterableFields() {
		return static::FILTERABLE_FIELDS;
	}

	/**
	 * Return Label for field
	 * @param  string $field
	 * @return string
	 */
	public function fieldLabel($field) {
		$label = $this->fieldAttribute($field, 'label');

		if ($label !== false) {
			return $label;
		}

		if (in_array($field, ['code', 'note'])) {
			return self::FIELD_ATTRIBUTES[$field]['label'];
		}
		return $field;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $id Note ID
	 * @return bool
	 */
	public function notesExist($id) {
		$q = $this->query();
		$q->filterById($id);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $id Note ID
	 * @return array
	 */
	public function getNotesArray($id) {
		$q = $this->query();
		$q->select(NotePredefined::aliasproperty('note'));
		$q->filterById($id);
		return $q->find()->toArray();
	}

	/**
	 * Return Note Line
	 * @param  string $id    Note ID
	 * @param  int    $line  Line Number
	 * @return NotePredefined
	 */
	public function noteLine($id, $line = 1) {
		$q = $this->query();
		$q->filterById($id);
		$q->filterBySequence($line);
		return $q->findOne();
	}

	/**
	 * Return JSON for Note
	 * @param  NotePredefined Note
	 * @return array
	 */
	public function json(NotePredefined $note) {
		$noteLines = $this->getNotesArray($note->code);

		$json = [
			'code'  => $note->code,
			'note'  => implode("\n", $noteLines),
			'lines' => $noteLines,
		];
		return $json;
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New NotePredefined
	 * @param  string $id  Note ID
	 * @return NotePredefined
	 */
	public function new($id = '') {
		$note = NotePredefined::new();
		if ($id && $id != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$note->setId($id);
		}
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
	public function deleteNotes($id) {
		$q = $this->query();
		$q->filterById($id);
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
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
		$this->deleteNotes($id);
		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($id);
			$note->generateKey2(); // PK
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
	 * @param  NotePredefined $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(NotePredefined $note, array $savedLines = []) {
		$response = new Response();
		$response->setKey($note->id);
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
		$id     = $values->text('code', ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);

		$note = $this->new($id);
		$response = $this->deleteAndRespond($note);

		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete note, update Dplus cobol
	 * @param  NotePredefined $note
	 * @return Response
	 */
	protected function deleteAndRespond(NotePredefined $note) {
		$success = $this->deleteNotes($note->id);

		$response = new Response();
		$response->setKey($note->id);
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
