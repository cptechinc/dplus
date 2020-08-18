<?php namespace ProcessWire;

/**
 * PrintLabelResponse
 * Handles Response Data for Print
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function Succeed?
 * @property bool    $error              Was there an error?
 * @property bool    $message            Error Message / Success Message
 * @property string  $key                Identifier of print
 *
 */
class PrintLabelResponse extends WireData {


	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->key   = '';
		$this->message = '';
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

	public function set_key($key) {
		$this->key = $key;
	}
}
