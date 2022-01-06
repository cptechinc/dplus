<?php namespace Dplus\UserOptions;

use ProcessWire\WireData;

/**
 * Response
 * Handles Response Data for Code Tables
 *
 * @author Paul Gomez
 *
 * @property bool    $success  Did the function Succeed?
 * @property bool    $error    Was there an error?
 * @property bool    $message  Error Message / Success Message
 * @property string  $userid   User ID
 * @property string  $function Function Code
 * @property string  $key      Key String
 */
class Response extends WireData {

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
		$this->message  = '';
		$this->userid   = '';
		$this->key      = '';
		$this->function = '';
		$this->fields   = [];
		$this->msgReplacements = [];
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

	public function setUserid($userid) {
		$this->userid = $userid;
	}

	public function setFunction($function) {
		$this->function = $function;
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

	public function addMsgReplacement($replace, $with) {
		$replacements = $this->msgReplacements;
		$replacements[$replace] = $with;
		$this->msgReplacements = $replacements;
	}

	protected function getPlaceholderReplaces() {
		$crud = self::CRUD_DESCRIPTION[$this->action];
		$replace = ['{userid}' => $this->userid, '{not}' => $this->hasSuccess() ? '' : 'not', '{crud}' => $crud];
		$replace = array_merge($replace, $this->msgReplacements);
		return $replace;
	}

	public function buildMessage($template) {
		$replace = $this->getPlaceholderReplaces();
		$msg = str_replace(array_keys($replace), array_values($replace), $template);
		$this->message = $msg;
	}

	public static function responseError($message) {
		$response = new Response();
		$response->message = $message;
		$response->setError(true);
		$response->setSuccess(false);
		return $response;
	}

	public static function responseSuccess($message) {
		$response = new Response();
		$response->message = $message;
		$response->setError(false);
		$response->setSuccess(true);
		return $response;
	}
}
