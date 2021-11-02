<?php namespace Dplus\Min\Inmain\Addm;

use ProcessWire\WireData;

/**
 * Response
 * Handles Response Data for Addm; functions
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function Succeed?
 * @property bool    $error              Was there an error?
 * @property int     $action             1 = CREATE | 2 = UPDATE | 3 = DELETE
 * @property bool    $message            Error Message / Success Message
 * @property string  $itemID             Item ID
 * @property string  $addonID            Add-on Item ID
 * @property string  $key                Record Key
 * @property array   $fields             Key-Value array of fields that need attention
 *
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
		$this->message = '';
		$this->itemID = '';
		$this->addonID = '';
		$this->key = '';
		$this->fields = array();
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

	public function getMessage() {
		return $this->data['message'];
	}

	public function setItemID($itemID) {
		$this->itemID = $itemID;
	}

	public function setAddonID($addonID) {
		$this->addonID = $addonID;
	}

	public function setSavedItm(bool $saved) {
		$this->savedItm = $saved;
	}

	public function setFields(array $fields) {
		$this->fields = $fields;
	}

	public function hasField($field) {
		return array_key_exists($field, $this->fields);
	}

	public function setKey($key) {
		$this->key = $key;
	}

	public function addMsgReplacement($replace, $with) {
		$replacements = $this->msgReplacements;
		$replacements[$replace] = $with;
		$this->msgReplacements = $replacements;
	}

	protected function getPlaceholderReplaces() {
		$crud = self::CRUD_DESCRIPTION[$this->action];
		$replace = ['{itemID}' => $this->itemID, '{not}' => $this->hasSuccess() ? '' : 'not', '{crud}' => $crud];
		if ($this->addonID) {
			$replace['{addonID}'] = $this->addonID;
		}
		$replace = array_merge($replace, $this->msgReplacements);
		return $replace;
	}

	public function buildMessage($template) {
		$replace = $this->getPlaceholderReplaces();
		$msg = str_replace(array_keys($replace), array_values($replace), $template);
		$this->message = $msg;
	}

	public static function responseError($key, $message) {
		$response = new Response();
		$response->itemID  = $itemID;
		$response->addonID = $addonID;
		$response->message = $message;
		$response->setError(true);
		$response->setSuccess(false);
		return $response;
	}

	public static function responseSuccess($key, $message) {
		$response = new Response();
		$response->itemID  = $itemID;
		$response->addonID = $addonID;
		$response->message = $message;
		$response->setError(false);
		$response->setSuccess(true);
		return $response;
	}
}
