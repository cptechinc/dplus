<?php namespace ProcessWire;

class CodeTablesResponse  {
	protected $success = false;
	protected $error = false;
	protected $message = '';
	protected $key = '';
	protected $action = 0;

	const CRUD_CREATE = 1;
	const CRUD_UPDATE = 2;
	const CRUD_DELETE = 3;

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
	 * Properties are protected from modification without function, but
	 * We want to allow the property values to be accessed
	 *
	 * @param  string $property  The $property trying to be accessed
	 * @return mixed			  property value or Error
	 */
	 public function __get($property) {
		$method = "get_".ucfirst($property);

		if (method_exists($this, $method)) {
			return $this->$method();
		} elseif (property_exists($this, $property)) {
			return $this->$property;
		}  else {
			$this->error("This property ($property) does not exist");
			return false;
		}
	}

	/**
	* Is used to PHP functions like isset() and empty() get access and see
	* if property is set
	* @param  string  $property Column Name
	* @return bool				 Whether $this->$property is set
	*/
	public function __isset($property) {
		if (isset($this->$property)) {
			return isset($this->$property);
		} else {
			return false;
		}
	}

}
