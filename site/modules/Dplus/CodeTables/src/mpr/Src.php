<?php namespace Dplus\Codes\Mpr;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
// Dplus Models
use ProspectSourceQuery, ProspectSource;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the SRC code table
 */
class Src extends Base {
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

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return ProspectSource
	 */
	public function new($id = '') {
		$code = new ProspectSource();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
