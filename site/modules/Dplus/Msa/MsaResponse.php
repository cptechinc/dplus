<?php namespace ProcessWire;

class MsaResponse extends WireData {

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;

	const CRUD_DESCRIPTION = [
		1 => 'created',
		2 => 'updated',
		3 => 'deleted'
	];

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
		$this->key = '';
		$this->action = 0;
		$this->fields = [];
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

	public function set_fields(array $fields) {
		$this->fields = $fields;
	}

	public function build_message($template) {
		$crud = self::CRUD_DESCRIPTION[$this->action];
		$replace = ['{key}' => $this->key, '{not}' => $this->has_success() ? '' : 'not', '{crud}' => $crud];
		$msg = str_replace(array_keys($replace), array_values($replace), $template);
		$this->message = $msg;
	}
}
