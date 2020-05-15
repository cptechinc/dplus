<?php namespace ProcessWire;

class ItmResponse extends WireData {

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;

	public function __construct() {
		$this->success = false;
		$this->error = false;
		$this->message = '';
		$this->itemID = '';
		$this->whseID = '';
		$this->action = 0;
		$this->saved_itm = false;
		$this->saved_itm_pricing = false;
		$this->saved_itm_whse = false;
		$this->saved_itm_costing = false;
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

	public function set_itemID($itemID) {
		$this->itemID = $itemID;
	}

	public function set_whseID($whseID) {
		$this->whseID = $whseID;
	}

	public function set_saved_itm(bool $saved) {
		$this->saved_itm = $saved;
	}

	public function set_saved_itm_pricing(bool $saved) {
		$this->saved_itm_pricing = $saved;
	}

	public function set_saved_itm_whse(bool $saved) {
		$this->saved_itm_whse = $saved;
	}

	public function set_saved_itm_costing(bool $saved) {
		$this->saved_itm_costing = $saved;
	}
}
