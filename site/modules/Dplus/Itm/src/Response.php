<?php namespace Dplus\Min\Inmain\Itm;

use ProcessWire\WireData;

/**
 * ItmResponse
 * Handles Response Data for Itm functions
 *
 * @author Paul Gomez
 *
 * @property bool    $success            Did the function Succeed?
 * @property bool    $error              Was there an error?
 * @property bool    $message            Error Message / Success Message
 * @property string  $itemID             Item ID
 * @property string  $whseID             Warehouse ID  ** Only for itm-whse
 * @property int     $action             1 = CREATE | 2 = UPDATE | 3 = DELETE
 * @property bool    $savedItm          Was ITM record Updated?
 * @property bool    $savedItmWhse     Was ITM Warehouse record Updated?
 * @property bool    $savedItmPricing  Was ITM Pricing record Updated?
 * @property bool    $savedItmCosting  Was ITM Pricing record Updated?
 * @property array   $fields             Key-Value array of fields that need attention
 *
 */
class Response extends WireData {

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;
	const CRUD_CLEAR  = 4;

	const CRUD_DESCRIPTION = [
		1 => 'created',
		2 => 'updated',
		3 => 'deleted',
		4 => 'cleared'
	];

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
		$this->itemID = '';
		$this->whseID = '';
		$this->action = 0;
		$this->savedItm = false;
		$this->savedItmPricing = false;
		$this->savedItmWhse = false;
		$this->savedItmCosting = false;
		$this->fields = array();
		$this->msgReplacements = [];
		$this->key = '';
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

	public function setWhseID($whseID) {
		$this->whseID = $whseID;
	}

	public function setSavedItm(bool $saved) {
		$this->savedItm = $saved;
	}

	public function setSavedItmPricing(bool $saved) {
		$this->savedItmPricing = $saved;
	}

	public function setSavedItmWhse(bool $saved) {
		$this->savedItmWhse = $saved;
	}

	public function setSavedItmCosting(bool $saved) {
		$this->savedItmCosting = $saved;
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
		$replace = ['{itemid}' => $this->itemID, '{not}' => $this->hasSuccess() ? '' : 'not', '{crud}' => $crud];
		if ($this->whseID) {
			$replace['{whseid}'] = $this->whseID;
		}
		$replace = array_merge($replace, $this->msgReplacements);
		return $replace;
	}

	public function buildMessage($template) {
		$replace = $this->getPlaceholderReplaces();
		$msg = str_replace(array_keys($replace), array_values($replace), $template);
		$this->message = $msg;
	}

	public static function responseError($itemID, $message) {
		$response = new Response();
		$response->itemID = $itemID;
		$response->message = $message;
		$response->setError(true);
		$response->setSuccess(false);
		return $response;
	}

	public static function responseSuccess($itemID, $message) {
		$response = new Response();
		$response->itemID = $itemID;
		$response->message = $message;
		$response->setError(false);
		$response->setSuccess(true);
		return $response;
	}
}
