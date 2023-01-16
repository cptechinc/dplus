<?php namespace Dplus\Qnotes;
// Dolus Models
use WarehouseNoteQuery, WarehouseNote;
// Dplus Configs
use Dplus\Configs;
// ProcessWire
use ProcessWire\WireInput;

class Iwhs extends Qnotes {
	const MODEL                = 'WarehouseNote';
	const MODEL_KEY            = 'id';
	const MODEL_TABLE          = 'notes_whse_invc_stmt';
	const DESCRIPTION          = 'Warehouse Note';
	const RESPONSE_TEMPLATE    = 'Warehouse Note {key} was {not} {crud}';
	const TYPE                 = 'IWHS';
	const TYPE_DESCRIPTION     = 'Invoice';
	const FIELD_ATTRIBUTES = [
		'whseid' => ['type' => 'text', 'maxlength' => 2],
		'note'   => ['type' => 'text', 'cols' => 35],
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
		return "Warehouse $desc Notes";
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
	 * @param  string $whseID Warehouse ID
	 * @param  string $type   Note Type @see WarehouseNote::TYPES
	 * @return bool
	 */
	public function notesExist($whseID) {
		$q = $this->query();
		$q->filterByWhseid($whseID);
		$q->filterByType(static::TYPE);
		return boolval($q->count());
	}

	/**
	 * Return Note Lines
	 * @param  string $whseID Note ID
	 * @return array
	 */
	public function getNotesArray($whseID) {
		$q = $this->query();
		$q->select(WarehouseNote::aliasproperty('note'));
		$q->filterByWhseid($whseID);
		$q->filterByType(static::TYPE);
		return $q->find()->toArray();
	}

	/**
	 * Return Note Line
	 * @param  string $whseID    Note ID
	 * @param  int    $line  Line Number
	 * @return WarehouseNote
	 */
	public function noteLine($whseID, $line = 1) {
		$q = $this->query();
		$q->filterByWhseid($whseID);
		$q->filterByType(static::TYPE);
		$q->filterBySequence($line);
		return $q->findOne();
	}

/* =============================================================
	CRUD Create Functions
============================================================= */
	/**
	 * Return New WarehouseNote
	 * @param  string $whseID  Warehouse ID
	 * @return WarehouseNote
	 */
	public function new($whseID) {
		$note = new WarehouseNote();
		$note->setWhseid($whseID);
		$note->setType(static::TYPE);
		$note->setDescription($this->dbDescription());
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
	 * @param  string $whseID  Warehouse ID
	 * @return bool
	 */
	public function deleteNotes($whseID) {
		$q = $this->query();
		$q->filterByWhseid($whseID);
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
		$whseID = $values->string('whseID');
		$this->deleteNotes($whseID);

		$noteLines = $this->explodeNoteLines($values->textarea('note'), $this->fieldAttribute('note', 'cols'));
		$savedLines = [];

		foreach ($noteLines as $key => $line) {
			$sequence = $key + 1;
			$note = $this->new($whseID);
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
	 * @param  WarehouseNote $note
	 * @param  array          $savedLines  e.g. [1 => true, 2 => false]
	 * @return Response
	 */
	private function updateAndRespond(WarehouseNote $note, array $savedLines = []) {
		$response = new Response();
		$response->setKey($note->whseid);
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
		$whseID = $values->string('whseID');

		$note = $this->new($whseID);
		$response = $this->deleteAndRespond($note);

		$this->setResponse($response);
		return $response->hasSuccess();
	}

	/**
	 * Delete note, update Dplus cobol
	 * @param  WarehouseNote $note
	 * @return Response
	 */
	protected function deleteAndRespond(WarehouseNote $note) {
		$success = $this->deleteNotes($note->whseid);

		$response = new Response();
		$response->setKey($note->whseid);
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
