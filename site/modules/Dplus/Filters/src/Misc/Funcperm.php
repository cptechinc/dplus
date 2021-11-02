<?php namespace Dplus\Filters\Misc;
// Dplus Model
use FuncpermQuery, Funcperm as Model;
// ProcessWire Classes
use ProcessWire\WireData, ProcessWire\WireInput, ProcessWire\Page;
// Dplus Filters
use Dplus\Filters\AbstractFilter;

/**
 * Wrapper Class for FuncpermQuery
 */
class Funcperm extends AbstractFilter {
	const MODEL = 'Funcperm';

/* =============================================================
	2. Base Filter Functions
============================================================= */
	/**
	 * Filter the Query By User ID
	 * @param  string $userID User ID
	 * @return self
	 */
	public function userid($userID) {
		$this->query->filterByLoginid($userID);
		return $this;
	}

	/**
	 * Filter the Query By User ID
	 * @param  string|array $functionID Function ID(s)
	 * @return self
	 */
	public function functionid($functionID) {
		if ($functionID) {
			$this->query->filterByFunction($function);
		}
		return $this;
	}

	/**
	 * Filter the Query By User ID
	 * @param  string       $userID     User ID
	 * @param  string|array $functionID Function ID(s)
	 * @return self
	 */
	public function useridAndFunctionid($userID, $functionID) {
		$this->userid($userID);
		$this->functionid($functionID);
		return $this;
	}

/* =============================================================
	4. Misc Query Functions
============================================================= */
	/**
	 * Return User's Permitted Functions
	 * @param  string       $userID      User ID
	 * @param  string|array $functionID  Function ID(s)
	 * @return array
	 */
	public function getUserFunctions($userID, $functionIDs = []) {
		$this->useridAndFunctionid($userID, $functionID);
		$this->query->select('function');
		return $this->query->find()->toArray();
	}
}
