<?php namespace Dplus\Qnotes;
// Dolus Models
use SalesOrderNotesQuery, SalesOrderNotes;
// ProcessWire
use ProcessWire\WireInput;
// Dplus Configs
use Dplus\Configs;

class Sord extends Qnotes {
	const MODEL                = 'SalesOrderNotes';
	const MODEL_KEY            = 'OehdNbr, OedtLine';
	const DESCRIPTION          = 'Sales Order Notes';
	const RESPONSE_TEMPLATE    = 'Sales Order Note {key} was {not} {crud}';
	const TYPE                 = 'SORD';

	const FIELD_ATTRIBUTES = [
		'ordn'   => ['type' => 'text'],
		'linenbr' => ['type' => 'int'],
		'note'    => ['type' => 'text', 'cols' => 50],
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

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Returns if Notes Exist
	 * @param  string $ordn    Order Number
	 * @param  int    $linenbr Line Number
	 * @return bool
	 */
	public function notesExist($ordn, $linenbr) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		$q->filterByLine($linenbr);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $ordn    Order Number
	 * @param  int    $linenbr Line Number
	 * @return array
	 */
	public function getNotesArray($ordn, $linenbr) {
		$q = $this->query();
		$q->select(SalesOrderNotes::aliasproperty('note'));
		$q->filterByOrdernumber($ordn);
		$q->filterByLine($linenbr);
		return $q->find()->toArray();
	}

	/**
	 * Returns Line 1 of Every Note
	 * @param  string $ordn    Order Number
	 * @param  int    $linenbr Line Number
	 * @return SalesOrderNotes[]|ObjectCollection
	 */
	public function getNotesSummarized($ordn, $linenbr) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		$q->filterByLine($linenbr);
		$q->filterBySequence(1);
		return $q->find();
	}

	/**
	 * Return Notes for Form
	 * @param  string $ordn    Order Number
	 * @param  int    $linenbr Line Number
	 * @param  string $form    Form to Match to e.g YYYN
	 * @return array
	 */
	public function getNotesFormArray($ordn, $linenbr, $form) {
		$q = $this->query();
		$q->select(SalesOrderNotes::aliasproperty('note'));
		$q->filterByOrdernumber($ordn);
		$q->filterByLine($linenbr);
		$q->filterByForm($form);
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New SalesOrderNotes
	 * @param  string $id  Note ID
	 * @return SalesOrderNotes
	 */
	public function new($id = '') {
		$note = SalesOrderNotes::new();
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
	 * @param  string $ordn    Order Number
	 * @param  int    $linenbr Line Number
	 * @param  string $form    Form to Match to e.g YYYN
	 * @return bool
	 */
	public function deleteNotes($ordn, $linenbr, $form) {
		$q = $this->query();
		$q->filterByOrdernumber($ordn);
		$q->filterByLine($linenbr);
		$q->filterByForm($form);

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
	 * @param  SalesOrderNotes $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(SalesOrderNotes $note, array $savedLines = []) {
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
	 * @param  SalesOrderNotes $note
	 * @return Response
	 */
	protected function deleteAndRespond(SalesOrderNotes $note) {
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
