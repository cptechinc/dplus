<?php namespace Dplus\Lookup\Item\Lookups;
//  ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput;
// Dplus Lookup
use Dplus\Lookup\Item\Input;
use Dplus\Lookup\Item\Sources\Factory;

/**
 * Base
 * Class that searches through sources to get item ID
 */
class Base extends WireData {
	const SOURCES = ['itm', 'cxm-shortitem'];

	/** @var Input */
	protected $inputdata;

	/** @var Results */
	protected $results;

	/**
	 * Set Input Data
	 * @param Input
	 */
	public function setInputDataFromWireInput(WireInput $data) {
		$this->inputdata = Input::fromWireInput($data);
	}

	/**
	 * Prepare InputData
	 */
	public function initInputData() {

	}

	/**
	 * Return Input Data
	 * @return Input
	 */
	public function getInputData() {
		return $this->inputdata;
	}

	/**
	 * Return Results
	 * @return Results
	 */
	public function getResults() {
		return $this->results;
	}

	/**
	 * Return Results Data
	 * @return array
	 */
	public function getResultsData() {
		return $this->results->getData();
	}

	/**
	 * Return if Item ID has been found
	 * @return bool
	 */
	public function find() {
		$this->results = new Results();
		$this->results->setInput($this->inputdata);

		if (empty($this->inputdata)) {
			$this->results->setMsg('No fields provided');
			return false;
		}

		$factory = new Factory();
		$factory->setInputData($this->inputdata);
		$sources = $factory->getSources(static::SOURCES);

		foreach ($sources as $key => $source) {
			$this->results->addSearched($key);
			$source->find();
			$results = $source->getResults();

			if ($results->exists()) {
				$this->results->setExists(true);
				$this->results->setItemid($results->itemid());
				$this->results->setCount($results->count());
				$this->results->setSource($key);
				return true;
			}
			$this->results->addMatchCount($key, $source->countMatches());
		}
		return false;
	}
}
