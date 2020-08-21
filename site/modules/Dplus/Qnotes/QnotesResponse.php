<?php namespace ProcessWire;

class QnotesResponse extends WireData {

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
		$this->key = '';
		$this->action = 0;
	}

	public function set_action(int $action = 0) {
		$this->action = $action;
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

	/**
	 * Return Error Response
	 * @param  string $key     Key of Note
	 * @param  string $message Error Message
	 * @param  int    $action
	 * @return QnotesResponse
	 */
	public static function response_error($key, $message, $action = 2) {
		$note = new QnotesResponse();
		$note->set_key($key);
		$note->set_message($message);
		$note->set_action($action);
		$note->set_success(false);
		$note->set_error(true);
		return $note;
	}

	/**
	 * Return Error Response
	 * @param  string $key     Key of Note
	 * @param  string $message Error Message
	 * @param  int    $action
	 * @return QnotesResponse
	 */
	public static function response_success($key, $message, $action = 2) {
		$note = new QnotesResponse();
		$note->set_key($key);
		$note->set_message($message);
		$note->set_action($action);
		$note->set_success(true);
		$note->set_error(false);
		return $note;
	}
}
