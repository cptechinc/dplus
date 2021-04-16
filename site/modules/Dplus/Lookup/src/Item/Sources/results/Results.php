<?php namespace Dplus\Lookup\Item\Sources;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Lookup
use Dplus\Lookup\Item\Input;

/**
 * Results
 * Data for Results from a Source
 */
class Results  extends WireData {
	protected $exists  = false;
	protected $count   = 0;
	protected $itemid  = '';
	protected $input   = null;
	protected $msg = '';

	public function exists() {
		return $this->exists;
	}

	public function setExists(bool $exists) {
		$this->exists = $exists;
	}

	public function count() {
		return $this->count;
	}

	public function setCount(int $count) {
		$this->count = $count;
	}

	public function input() {
		return $this->input;
	}

	public function setInput(Input $input) {
		$this->input = $input;
	}

	public function itemid() {
		return $this->itemid;
	}

	public function setItemid(string $itemid) {
		$this->itemid = $itemid;
	}

	public function msg() {
		return $this->msg;
	}

	public function setMsg($msg) {
		$this->msg = $msg;
	}

	/**
	 * Return Data
	 * @return array
	 */
	public function getData() {
		return [
			'exists'  => $this->exists,
			'itemid'  => $this->itemid,
			'count'   => $this->count,
			'msg'     => $this->msg,
			'input'   => $this->input->getDataNotEmpty(),
		];
	}
}
