<?php namespace Dplus\Lookup\Item\Lookups;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Lookup
use Dplus\Lookup\Item\InputData;
use Dplus\Lookup\Item\Sources\Results as SourceResults;

/**
 * Results
 * Data for Results from a lookup
 */
class Results extends SourceResults {
	protected $source   = '';
	protected $searched = [];
	protected $matches  = [];

	/**
	 * Set Source of Result if exists
	 * @param string $code
	 */
	public function setSource($code) {
		$this->source = $code;
	}


	/**
	 * Return Data
	 * @return array
	 */
	public function getData() {
		$data = parent::getData();
		$data['source']   = $this->source;
		$data['searched'] = implode(',', $this->searched);
		$data['matches']  = $this->matches;
		return $data;
	}

	public function addSearched($code) {
		$this->searched[] = $code;
	}

	public function addMatchCount($code, int $count) {
		$this->matches[$code] = $count;
	}
}
