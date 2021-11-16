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

class Password extends Logm {
	const FIELD_ATTRIBUTES = [
		'faxname'      => ['type' => 'text', 'maxlength' => 30],
		'faxcompany'   => ['type' => 'text', 'maxlength' => 30],
		'coversheet'   => ['type' => 'text', 'maxlength' => 8],
		'email'        => ['type' => 'text', 'maxlength' => 50],
		'faxsubject'   => ['type' => 'text', 'maxlength' => 40],
		'sendtime'     => ['type' => 'text', 'options' => DplusUser::SENDTIMES],
		'notify'       => ['type' => 'text', 'true' => DplusUser::NOTIFY_TRUE]
	];

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
	 * Update Logm User Data
	 * @param  WireInput $input Input Data
	 * @param  DplusUser $user  User
	 * @return bool
	 */
	protected function updateInputUser(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values  = $input->$rm;
		$invalid = [];


		$this->updateInputUserPassword($input, $user);

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

	/**
	 * Update Email, Phones
	 * @param  WireInput $input Input Data
	 * @param  DplusUser $user  User
	 * @return void
	 */
	private function updateInputUserPassword(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		
	}
}
