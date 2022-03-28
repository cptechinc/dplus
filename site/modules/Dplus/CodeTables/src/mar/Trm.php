<?php namespace Dplus\Codes\Mar;
// Purl URI Library
use Purl\Url;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Model;
// Dplus Models
use ArTermsCodeQuery, ArTermsCode;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TRM code table
 */
class Trm extends Base {
	const MODEL              = 'ArTermsCode';
	const MODEL_KEY          = 'code';
	const MODEL_TABLE        = 'ar_term_code';
	const DESCRIPTION        = 'Customer Terms Code';
	const DESCRIPTION_RECORD = 'Customer Terms Code';
	const RESPONSE_TEMPLATE  = 'Customer Terms Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'trm';
	const DPLUS_TABLE           = 'TRM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 4],
		'description' => ['type' => 'text', 'maxlength' => 20],
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
	 * @return ArTermsCode
	 */
	public function new($id = '') {
		$code = new ArTermsCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}
}
