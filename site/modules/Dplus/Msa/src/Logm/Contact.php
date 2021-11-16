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

		$user->setFaxname($values->text('faxname', ['maxLength' => $this->fieldAttribute('faxname', 'maxlength')]));
		$user->setFaxcompany($values->text('faxcompany', ['maxLength' => $this->fieldAttribute('faxcompany', 'maxlength')]));
		$user->setCoversheet($values->text('coversheet', ['maxLength' => $this->fieldAttribute('coversheet', 'maxlength')]));
		$user->setFaxsubject($values->text('faxsubject', ['maxLength' => $this->fieldAttribute('faxsubject', 'maxlength')]));
		$this->updateInputUserEmail($input, $user);
		$this->updateInputUserPhones($input, $user);
		$this->updateInputUserNotify($input, $user);
		$this->updateInputUserSendtime($input, $user);

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
	 * Update Email
	 * @param  WireInput $input Input Data
	 * @param  DplusUser $user  User
	 * @return void
	 */
	private function updateInputUserEmail(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$sanitizer = $this->wire('sanitizer');

		$email = $values->email('email');
		$email = $sanitizer->text($email, ['maxLength' => $this->fieldAttribute('email', 'maxlength')]);
		$user->setEmail($email);
	}

	/**
	 * Update Phone and Fax Numbers
	 * @param  WireInput $input  Input Data
	 * @param  DplusUser $user   User
	 * @return void
	 */
	private function updateInputUserPhones(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$fields = ['fax', 'phone'];
		$subfields = ['area', 'first3', 'last4'];

		foreach($fields as $f) {
			$nbr = $values->array($f, 'text', ['delimiter' => '-']);
			if (sizeof($nbr) !== 3) {
				$nbr = ['', '', ''];
			}

			foreach ($subfields as $index => $subf) {
				$setFunc = 'set'. ucfirst($f) . $subf;
				$user->$setFunc($nbr[$index]);
			}
		}
	}

	/**
	 * Update Notify fields for User
	 * @param  WireInput $input Input Data
	 * @param  DplusUser $user  User
	 * @return void
	 */
	private function updateInputUserNotify(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;

		$user->setNotifysuccess($values->xorblank('notifysuccess'));
		$user->setNotifyfailure($values->xorblank('notifyfailure'));
	}

	/**
	 * Update sendtime for User
	 * @param  WireInput $input Input Data
	 * @param  DplusUser $user  User
	 * @return void
	 */
	private function updateInputUserSendtime(WireInput $input, DplusUser $user) {
		$rm = strtolower($input->requestMethod());
		$values = $input->$rm;
		$value = $values->option('sendtime', array_keys($this->fieldAttribute('sendtime', 'options')));
		$user->setSendtime($value);
	}
}
