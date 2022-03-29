<?php namespace Dplus\Codes\Mar;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use CountryCodeQuery, CountryCode;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the COCOM code table
 * TODO: FINISH
 */
class Cocom extends Base {
	const MODEL              = 'CountryCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'country_codes';
	const DESCRIPTION        = 'Country Code';
	const DESCRIPTION_RECORD = 'Country Code';
	const RESPONSE_TEMPLATE  = 'Country Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'cocom';
	const DPLUS_TABLE           = 'COCOM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 3],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	/** @var self */
	protected static $instance;

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		return [
			'code' => $code->code,
			'iso3' => $code->iso3,
			'name' => $code->description,
			'description' => $code->description,
			'exchangerate' => $code->exchange_rate
		];
	}

/* =============================================================
	CRUD Creates
============================================================= */
}
