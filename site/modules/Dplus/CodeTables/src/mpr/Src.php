<?php namespace Dplus\Codes\Mpr;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use ProspectSourceQuery, ProspectSource;
// Dplus Codes
use Dplus\Codes\AbstractCodeTableEditableSingleKey;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SRC code table
 */
class Src extends AbstractCodeTableEditableSingleKey {
	const MODEL              = 'ProspectSource';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'prosp_sorc_code';
	const DESCRIPTION        = 'Source Code';
	const DESCRIPTION_RECORD = 'Source Code';
	const RESPONSE_TEMPLATE  = 'Source Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'src';
	const DPLUS_TABLE           = 'SRC';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => ProspectSource::CODELENGTH],
		'description' => ['type' => 'text', 'maxlength' => 30],
	];

	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Source Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(ProspectSource::aliasproperty('id'));
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
}
