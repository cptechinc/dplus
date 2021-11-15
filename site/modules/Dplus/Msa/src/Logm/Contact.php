<?php namespace Dplus\Msa\Logm;
// Dplus Models
use DplusUserQuery, DplusUser;
// ProcessWire
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Record Locker
use Dplus\RecordLocker\UserFunction as FunctionLocker;
// Dplus Validators
use Dplus\CodeValidators as Validators;
// Dplus MSA
use Dplus\Msa\Logm;

class Contact extends Logm {
	const FIELD_ATTRIBUTES = [
		'faxname'      => ['type' => 'text', 'maxlength' => 30],
		'faxcompany'   => ['type' => 'text', 'maxlength' => 30],
		'coversheet'   => ['type' => 'text', 'maxlength' => 8],
		'email'        => ['type' => 'text', 'maxlength' => 50],
		'faxsubject'   => ['type' => 'text', 'maxlength' => 40],
	];

	public function __construct() {
		$this->sessionID = session_id();

		$this->recordlocker = new FunctionLocker();
		$this->recordlocker->setFunction(self::RECORDLOCKER_FUNCTION);
		$this->recordlocker->setUser($this->wire('user'));
	}

	private static $instance;

	public static function getInstance() {
		if (empty(self::$instance)) {
			$instance = new self();
			self::$instance = $instance;
		}
		return self::$instance;
	}

/* =============================================================
	Input Functions
============================================================= */
	/**
	 * Process Input Data
	 * @param  WireInput $input Input Data
	 * @return void
	 */
	public function processInput(WireInput $input) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		switch ($values->text('action')) {
			case 'update':
				$this->updateInput($input);
				break;
		}
	}

	/**
	 * Update Logm User Data
	 * @param  WireInput $input Input Data
	 * @return bool
	 */
	protected function updateInputUser(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$invalid = [];

		$user->setName($values->text('name', ['maxLength' => $this->fieldAttribute('name', 'maxlength')]));
		$user->setCompanyid($values->text('companyid', ['maxLength' => $this->fieldAttribute('companyid', 'maxlength')]));
		$user->setAdmin($values->yn('admin'));
		$user->setStorefront($values->yn('storefront'));
		$user->setCitydesk($values->yn('citydesk'));
		$user->setReportadmin($values->yn('reportadmin'));
		$user->setUserwhsefirst($values->yn('userwhsefirst'));
		$user->setActiveitemsonly($values->yn('activteitemsonly'));
		$user->setRestrictaccess($values->yn('restrictaccess'));
		$user->setAllowprocessdelete($values->yn('allowprocessdelete'));
		$user->setDate(date('Ymd'));
		$user->setTime(date('His'));

		$response = $this->saveAndRespond($user, $invalid);
		if ($response->fields) {
			$response->setError(true);
			$response->setSuccess(false);
			$response->buildMessage(self::RESPONSE_TEMPLATE);
		}
		$this->setResponse($response);
		return $response->hasSuccess();
	}
}
