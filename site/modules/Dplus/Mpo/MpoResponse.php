<?php namespace ProcessWire;

/**
 * MpoResponse
 * Class for Getting Responses out of the MPO set of functions
 *
 * @property bool   $success  Did action Succeed?
 * @property bool   $error    Did Error Occur?
 * @property string $message  Success / Error Message
 * @property string $po       Purchase Order Number
 * @property int    $action   CRUD_* action
 * @property int    $section  SECTION_*
 * @property int    $line     Line Number
 * @property array  $fields   Key-Value array of fields that need attention
 */
class MpoResponse extends WireData {

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;

	const SECTION_HEADER = 0;
	const SECTION_DETAIL = 1;

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
		$this->ponbr = '';
		$this->action = 0;
		$this->section = 0;
		$this->line = 0;
		$this->fields = array();
	}

	public function has_success() {
		return boolval($this->success);
	}

	public function has_error() {
		return boolval($this->error);
	}
	public function set_success(bool $success) {
		$this->success = $success;
	}

	public function set_error(bool $error) {
		$this->error = $error;
	}

	public function set_message($message) {
		$this->message = $message;
	}

	public function set_ponbr($ponbr) {
		$this->ponbr = $ponbr;
	}

	public function set_action(int $action = 0) {
		$this->action = $action;
	}

	public function set_section(int $section = 0) {
		$this->section = $section;
	}

	public function set_line(int $line = 0) {
		$this->line = $line;
	}

	public function set_fields(array $fields) {
		$this->fields = $fields;
	}

	public function has_field($field) {
		return array_key_exists($field, $this->fields);
	}

	public static function response_error($ponbr, $message) {
		$response = new MpoResponse();
		$response->ponbr = $ponbr;
		$response->message = $message;
		$response->set_error(true);
		$response->set_success(false);
		return $response;
	}

	public static function response_success($ponbr, $message) {
		$response = new MpoResponse();
		$response->ponbr = $ponbr;
		$response->message = $message;
		$response->set_error(false);
		$response->set_success(true);
		return $response;
	}
}
