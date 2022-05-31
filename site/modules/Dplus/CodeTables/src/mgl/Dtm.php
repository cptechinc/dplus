<?php namespace Dplus\Codes\Mgl;
// Propel Classes
use Propel\Runtime\Collection\ObjectCollection;
use Propel\Runtime\ActiveRecord\ActiveRecordInterface as Code;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Models
use GlDistCodeQuery, GlDistCode;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus Codes
use Dplus\Codes\Base\Simple as Base;
use Dplus\Codes\Response;

/**
 * Class that handles the CRUD of the TTM code table
 */
class Dtm extends Base {
	const MODEL              = 'GlDistCode';
	const MODEL_KEY          = 'id';
	const MODEL_TABLE        = 'gl_dist_code';
	const DESCRIPTION        = 'Distribution Code';
	const DESCRIPTION_RECORD = 'Distribution Code';
	const RESPONSE_TEMPLATE  = 'Distribution Code {code} {not} {crud}';
	const RECORDLOCKER_FUNCTION = 'dtm';
	const DPLUS_TABLE           = 'DTM';
	const FIELD_ATTRIBUTES = [
		'code'        => ['type' => 'text', 'maxlength' => 6],
		'description' => ['type' => 'text', 'maxlength' => 20],
	];

	protected static $instance;

	/**
	 * Return the number of GL accounts
	 * @return int
	 */
	public function getNbrOfGlAccts() {
		return GlDistCode::NBROFACCTS;
	}

	/**
	 * Return Array ready for JSON
	 * @param  Code  $code Code
	 * @return array
	 */
	public function codeJson(Code $code) {
		$mhm = Mhm::getInstance();
		$json = [
			'code'        => $code->code,
			'description' => $code->description,
			'accounts'    => []
		];

		for ($i = 1; $i <= $this->getNbrOfGlAccts(); $i++) {
			$acctNbr = $code->getAccountNbr($i);
			$json['accounts'][$i] = [
				'code'        => '',
				'description' => '',
				'percent'     => ''
			];

			if ($acctNbr && $mhm->exists($acctNbr)) {
				$glAcct = $mhm->code($acctNbr);

				$json['accounts'][$i] = [
					'code'        => $glAcct->code,
					'description' => $glAcct->description,
					'percent'     => $code->getAccountPct($i),
				];
			}
		}
		return $json;
	}

/* =============================================================
	CRUD Read, Validate Functions
============================================================= */
	/**
	 * Return the IDs for the Work Center Confirm Code
	 * @return array
	 */
	public function ids() {
		$q = $this->query();
		$q->select(GlDistCode::aliasproperty('id'));
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

/* =============================================================
	CRUD Creates
============================================================= */
	/**
	 * Return New Code
	 * @return GlDistCode
	 */
	public function new($id = '') {
		$code = new GlDistCode();
		if (empty($id) === false && strtolower($id) != 'new') {
			$id = $this->wire('sanitizer')->text($id, ['maxLength' => $this->fieldAttribute('code', 'maxlength')]);
			$code->setId($id);
		}
		return $code;
	}

/* =============================================================
	CRUD Processing
============================================================= */
	/**
	 * Update Record with Input Data
	 * @param  WireInput $input Input Data
	 * @param  Code      $code
	 * @return array
	 */
	protected function _inputUpdate(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$invalidfields = parent::_inputUpdate($input, $code);
		$invalidfields = array_merge($invalidfields, $this->updateGlAcctsPcts($input, $code));
		return $invalidfields;
	}

	/**
	 * Set GL Accounts and Percentages
	 * @param  WireInput $input  Input Data
	 * @param  Code      $code
	 * @return array
	 */
	private function updateGlAcctsPcts(WireInput $input, Code $code) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$total = $subtotal = 0;
		$validate = new Validators\Mgl();
		$invalidfields = [];

		for ($i = 1; $i <= $this->getNbrOfGlAccts(); $i++) {
			if ($values->text("glacct$i") != '' && $validate->glCode($values->text("glacct$i")) === false) {
				$invalidfields["glacct$i"] = "GL Account $i";
				continue;
			}
			$code->setAccountNbr($i, $values->text("glacct$i"));
			$subtotal += $values->float("glpct$i");

			if ($subtotal <= 100) {
				$code->setAccountPct($i, $values->float("glpct$i"));
				$total += $values->float("glpct$i");
			}
		}
		if ($total != 100) {
			$invalidfields['glpcttotal'] = "GL Percent Total is not equal 100";
		}
		return $invalidfields;
	}
}
