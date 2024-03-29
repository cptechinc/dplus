<?php namespace Dplus\Xrefs;

/**
 * Response
 * Handles Response Data for Xref functions
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function Succeed?
 * @property bool    $error              Was there an error?
 * @property bool    $message            Error Message / Success Message
 * @property string  $key                Key
 * @property int     $action             1 = CREATE | 2 = UPDATE | 3 = DELETE
 * @property array   $fields             Key-Value array of fields that need attention
 *
 */
class Response {
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
		$this->action = 0;
		$this->message = '';
		$this->key = '';
		$this->fields = array();
	}

	public function setAction(int $action = 0) {
		$this->action = $action;
	}

	public function hasSuccess() {
		return boolval($this->success);
	}

	public function hasError() {
		return boolval($this->error);
	}

	public function setSuccess(bool $success) {
		$this->success = $success;
	}

	public function setError(bool $error) {
		$this->error = $error;
	}

	public function setMessage($message) {
		$this->message = $message;
	}

	public function setKey($key) {
		$this->key = $key;
	}

	public function setFields(array $fields) {
		$this->fields = $fields;
	}

	public function hasField($field) {
		return array_key_exists($field, $this->fields);
	}

	public function buildMessage($template) {
		$crud = self::CRUD_DESCRIPTION[$this->action];
		$replace = ['{key}' => $this->key, '{not}' => $this->hasSuccess() ? '' : 'not', '{crud}' => $crud];
		$msg = str_replace(array_keys($replace), array_values($replace), $template);
		$this->message = $msg;
	}

	public static function responseError($key, $message) {
		$response = new Response();
		$response->key = $key;
		$response->message = $message;
		$response->setError(true);
		$response->setSuccess(false);
		return $response;
	}

	public static function responseSuccess($key, $message) {
		$response = new Response();
		$response->key = $key;
		$response->message = $message;
		$response->setError(false);
		$response->setSuccess(true);
		return $response;
	}
}
