<?php namespace Dplus\Codes\Min\Tarm;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use TariffCodeCountryQuery, TariffCodeCountry;
use CountryCodeQuery, CountryCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Filters
use Dplus\Filters;
// Dplus Configs
use Dplus\Configs;
// Dplus Codes
use Dplus\Codes;
use Dplus\Codes\Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of TARM countries
 */
class Countries extends Base {
	const MODEL              = 'TariffCodeCountry';
	const MODEL_KEY          = 'id, country';
	const MODEL_TABLE        = 'inv_trco_code';

	/**
	 * Return Query filtered By Code
	 * @param  string $id  Code
	 * @return TariffCodeCountryQuery
	 */
	public function queryCode($id) {
		$q = $this->query();
		$q->filterByCode($id);
		return $q;
	}

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return new TariffCodeCountry
	 * @param  string $id  Code
	 * @param  string $iso3 ISO3 Country Code
	 * @return TariffCodeCountry
	 */
	public function new($id = '', $iso3) {
		$code = new TariffCodeCountry();
		$code->setId($id);
		$code->setCountry($iso3);
		return $code;
	}

	/**
	 * Delete All records for Code
	 * @param  string $id Tariff Code
	 * @return bool
	 */
	public function deleteForCode($id) {
		$q = $this->queryCode($id);
		if ($q->count() == 0) {
			return true;
		}
		return boolval($q->delete());
	}

	/**
	 * Return Number of Records for Tariff Code
	 * @param  string $id Tariff Code
	 * @return int
	 */
	public function countForCode($id) {
		return $this->queryCode($id)->count();
	}

	/**
	 * Return Records found for code
	 * @param  string $id Tariff Code
	 * @return ObjectCollection
	 */
	public function findForCode($id) {
		return $this->queryCode($id)->find();
	}

	/**
	 * Return Country Codes
	 * @param  string $id Tariff Code
	 * @return array
	 */
	public function codesForTariffCode($id) {
		$q = $this->queryCode($id);
		$q->select(TariffCodeCountry::aliasproperty('country'));
		return $q->find()->toArray();
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputUpdate(WireInput $input) {}

	/**
	 * Delete Code from Input Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function inputDelete(WireInput $input) {}

/* =============================================================
	Supplemental
============================================================= */
	/**
	 * Return All Country Codes
	 * @return ObjectCollection
	 */
	public function getAllCountryCodes() {
		$filter = new Filters\Misc\CountryCode();
		return $filter->query->find();
	}
}
