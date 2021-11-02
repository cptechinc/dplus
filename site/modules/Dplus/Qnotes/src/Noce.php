<?php namespace Dplus\Qnotes;
// Dolus Models
use NotePredefinedQuery, NotePredefined;

class Noce extends Qnotes {
	const MODEL                = 'NotePredefined';
	const MODEL_KEY            = 'id';
	const DESCRIPTION          = 'Pre-Defined Notes';
	const RESPONSE_TEMPLATE    = 'Pre-Defined Note {code} {not} {crud}';
	const TYPE                 = 'NOCE';

	const FIELD_ATTRIBUTES = [
		'code' => ['type' => 'text', 'maxlength' => NotePredefined::MAX_LENGTH_CODE],
		'note' => ['type' => 'text', 'maxlength' => 50],
	];

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



}
