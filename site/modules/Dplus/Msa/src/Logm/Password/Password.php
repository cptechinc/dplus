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
	const PSWD_SHELL = '/usr/capsys/menu/password/password';
	const FIELD_ATTRIBUTES = [];

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
	protected function updateInputUserPassword(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$password = $values->text('password');
		$cmd = 'php ' . self::PSWD_SHELL . " hash password=$password";
		$password = $this->wire('sanitizer')->text(shell_exec($cmd));
		$user->setPassword($password);
	}
}
